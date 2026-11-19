<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    private const METODE_KEY = [
        'QRIS'     => 'metode_qris',
        'E-Wallet' => 'metode_ewallet',
        'Tunai'    => 'metode_tunai',
    ];

    private const ONLINE = ['QRIS', 'E-Wallet'];

    public function index()
    {
        // Tagihan online yang kedaluwarsa: batalkan dan kembalikan stoknya
        Transaction::where('status', 'menunggu')->where('kadaluarsa_at', '<', now())
            ->get()->each->batalkan();

        $produk = Product::orderBy('nama')->get()->map(fn ($p) => [
            'id'       => $p->id,
            'nama'     => $p->nama,
            'kode'     => $p->kode,
            'kategori' => $p->kategori,
            'harga'    => (int) $p->harga_jual,
            'stok'     => (int) $p->stok,
            'gambar'   => $p->gambar && file_exists(public_path('images/produk/' . $p->gambar))
                            ? asset('images/produk/' . $p->gambar) : null,
        ])->values();

        return view('pos.index', [
            'produk'   => $produk,
            'metode'   => $this->metodeAktif(),
            'kategori' => Product::KATEGORI,
            'info'     => [],
        ]);
    }

    // Tunai: langsung lunas
    public function store(Request $request)
    {
        $data = $request->validate(
            $this->aturanDasar(['Tunai']) + ['bayar' => ['required', 'integer', 'min:0']],
            $this->pesan()
        );

        $trx = $this->buat($data, $request->user(), 'lunas', (int) $data['bayar']);

        return response()->json($this->ringkas($trx));
    }

    // QRIS / GoPay: transaksi "menunggu", lalu minta kode ke Midtrans
    public function online(Request $request, MidtransService $mid)
    {
        $data = $request->validate($this->aturanDasar(self::ONLINE), $this->pesan());

        $trx = $this->buat($data, $request->user(), 'menunggu');

        try {
            $r = $data['metode'] === 'QRIS'
                ? $mid->buatQris($trx->ref_id, (int) $trx->total)
                : $mid->buatGopay($trx->ref_id, (int) $trx->total);

            if (! $r['qr_string'] && ! $r['qr_url']) {
                throw new \RuntimeException('Midtrans tidak mengirim kode pembayaran.');
            }

            $trx->update(['gateway_id' => $r['id'], 'qr_string' => $r['qr_string'], 'qr_url' => $r['qr_url']]);
        } catch (\Throwable $e) {
            report($e);
            $trx->batalkan();

            return response()->json(['message' => 'Gagal membuat pembayaran: ' . $e->getMessage()], 502);
        }

        return response()->json($this->ringkas($trx) + [
            'qr_string'  => $r['qr_string'],
            'qr_url'     => $r['qr_url'],
            'sisa_detik' => max(0, now()->diffInSeconds($trx->kadaluarsa_at, false)),
            'status_url' => route('pos.status', $trx),
            'batal_url'  => route('pos.batal', $trx),
        ]);
    }

    // Dipanggil layar kasir tiap beberapa detik
    public function status(Request $request, Transaction $transaction, MidtransService $mid)
    {
        $this->izin($request, $transaction);

        if ($transaction->status === 'menunggu') {
            try {
                $res = $mid->status($transaction->ref_id);
                $transaction->terapkanStatus((string) ($res['transaction_status'] ?? ''));
            } catch (\Throwable $e) {
                report($e);
            }

            $transaction->refresh();
            if ($transaction->status === 'menunggu' && $transaction->kadaluarsa_at?->isPast()) {
                $transaction->batalkan();
            }
        }

        return response()->json($this->ringkas($transaction->refresh()));
    }

    public function batal(Request $request, Transaction $transaction, MidtransService $mid)
    {
        $this->izin($request, $transaction);

        if ($transaction->status === 'lunas') {
            return response()->json(['message' => 'Pembayaran sudah diterima.'], 409);
        }

        try {
            $mid->batal($transaction->ref_id);
        } catch (\Throwable $e) {
            report($e);
        }
        $transaction->batalkan();

        return response()->json(['ok' => true]);
    }

    public function struk(Request $request, Transaction $transaction)
    {
        $this->izin($request, $transaction);
        $transaction->load(['items', 'kasir']);

        return view('pos.struk', [
            't'     => $transaction,
            's'     => Setting::semua(),
            'cetak' => $request->boolean('cetak'),
        ]);
    }

    // ---------- Helper ----------

    private function aturanDasar(array $metodeBoleh): array
    {
        return [
            'items'       => ['required', 'array', 'min:1', 'max:100'],
            'items.*.id'  => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:999'],
            'diskon'      => ['nullable', 'integer', 'min:0', 'max:100'],
            'metode'      => ['required', Rule::in(array_values(array_intersect($this->metodeAktif(), $metodeBoleh)))],
        ];
    }

    private function pesan(): array
    {
        return [
            'items.required'  => 'Keranjang masih kosong.',
            'items.min'       => 'Keranjang masih kosong.',
            'metode.required' => 'Pilih metode pembayaran.',
            'metode.in'       => 'Metode pembayaran tidak aktif.',
            'diskon.max'      => 'Diskon maksimal 100%.',
        ];
    }

    // Stok dikunci lalu ditahan (dikurangi) sejak transaksi dibuat
    private function buat(array $data, User $user, string $status, int $bayar = 0): Transaction
    {
        return DB::transaction(function () use ($data, $user, $status, $bayar) {
            $products = Product::whereIn('id', collect($data['items'])->pluck('id'))
                ->lockForUpdate()->get()->keyBy('id');

            $subtotal = 0;
            $baris    = [];

            foreach ($data['items'] as $it) {
                $p = $products[$it['id']];

                if ($p->stok < $it['qty']) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$p->nama} tidak mencukupi (sisa {$p->stok}).",
                    ]);
                }

                $sub       = $p->harga_jual * $it['qty'];   // harga dari database
                $subtotal += $sub;
                $baris[]   = [$p, (int) $it['qty'], $sub];
            }

            $persen = (int) ($data['diskon'] ?? 0);
            $diskon = intdiv($subtotal * $persen, 100);
            $total  = $subtotal - $diskon;
            $online = $status === 'menunggu';

            if ($online && $total <= 0) {
                throw ValidationException::withMessages(['items' => 'Total harus lebih dari Rp 0 untuk pembayaran online.']);
            }
            if (! $online && $bayar < $total) {
                throw ValidationException::withMessages(['bayar' => 'Uang yang diterima kurang dari total.']);
            }

            $terakhir = Transaction::lockForUpdate()->orderByDesc('id')->value('kode');
            $nomor    = $terakhir ? ((int) preg_replace('/\D/', '', $terakhir)) + 1 : 1;
            $kode     = 'TRX-' . str_pad($nomor, 4, '0', STR_PAD_LEFT);

            $trx = Transaction::create([
                'kode'           => $kode,
                'user_id'        => $user->id,
                'tanggal'        => now(),
                'metode'         => $data['metode'],
                'status'         => $status,
                'subtotal'       => $subtotal,
                'diskon_persen'  => $persen,
                'diskon_nominal' => $diskon,
                'total'          => $total,
                'bayar'          => $online ? 0 : $bayar,
                'kembali'        => $online ? 0 : $bayar - $total,
                'ref_id'         => $online ? $kode . '-' . Str::upper(Str::random(5)) : null,
                'kadaluarsa_at'  => $online ? now()->addMinutes(15) : null,
            ]);

            foreach ($baris as [$p, $qty, $sub]) {
                TransactionItem::create([
                    'transaction_id' => $trx->id,
                    'product_id'     => $p->id,
                    'nama_produk'    => $p->nama,
                    'harga'          => $p->harga_jual,
                    'jumlah'         => $qty,
                    'subtotal'       => $sub,
                ]);

                $p->decrement('stok', $qty);
            }

            return $trx;
        });
    }

    private function ringkas(Transaction $t): array
    {
        return [
            'ok'        => true,
            'id'        => $t->id,
            'status'    => $t->status,
            'kode'      => $t->kode,
            'total'     => $t->total,
            'kembali'   => $t->kembali,
            'struk_url' => route('pos.struk', $t),
        ];
    }

    // Kasir hanya boleh mengakses transaksinya sendiri
    private function izin(Request $request, Transaction $t): void
    {
        $u = $request->user();
        abort_unless($u->role !== 'kasir' || $t->user_id === $u->id, 403);
    }

    private function metodeAktif(): array
    {
        $s = Setting::semua();

        return array_values(array_filter(
            Transaction::METODE,
            fn ($m) => isset(self::METODE_KEY[$m]) && ($s[self::METODE_KEY[$m]] ?? '0') === '1'
        ));
    }
}