<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    // Nama metode => key pengaturan
    private const METODE_KEY = [
        'QRIS'          => 'metode_qris',
        'Transfer Bank' => 'metode_transfer',
        'E-Wallet'      => 'metode_ewallet',
        'Tunai'         => 'metode_tunai',
    ];

    public function index()
    {
        $s = Setting::semua();

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

        $info = [
            'bank'    => $s['bank_nama'] ? trim("{$s['bank_nama']} {$s['bank_rekening']} a.n. {$s['bank_atas_nama']}") : null,
            'ewallet' => $s['ewallet_nomor'] ?: null,
        ];

        return view('pos.index', [
            'produk'   => $produk,
            'metode'   => $this->metodeAktif(),
            'kategori' => Product::KATEGORI,
            'info'     => $info,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items'        => ['required', 'array', 'min:1', 'max:100'],
            'items.*.id'   => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.qty'  => ['required', 'integer', 'min:1', 'max:999'],
            'diskon'       => ['nullable', 'integer', 'min:0', 'max:100'],
            'metode'       => ['required', Rule::in($this->metodeAktif())],
            'bayar'        => ['nullable', 'integer', 'min:0'],
        ], [
            'items.required' => 'Keranjang masih kosong.',
            'items.min'      => 'Keranjang masih kosong.',
            'metode.required'=> 'Pilih metode pembayaran.',
            'metode.in'      => 'Metode pembayaran tidak aktif.',
            'diskon.max'     => 'Diskon maksimal 100%.',
        ]);

        $trx = DB::transaction(function () use ($data, $request) {
            // Kunci baris produk supaya stok tidak ditimpa kasir lain
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

                $sub       = $p->harga_jual * $it['qty'];   // harga dari database, bukan dari browser
                $subtotal += $sub;
                $baris[]   = [$p, (int) $it['qty'], $sub];
            }

            $persen = (int) ($data['diskon'] ?? 0);
            $diskon = intdiv($subtotal * $persen, 100);
            $total  = $subtotal - $diskon;

            $tunai = $data['metode'] === 'Tunai';
            $bayar = $tunai ? (int) ($data['bayar'] ?? 0) : $total;

            if ($bayar < $total) {
                throw ValidationException::withMessages(['bayar' => 'Uang yang diterima kurang dari total.']);
            }

            // Kode berikutnya: TRX-0001, TRX-0002, ...
            $terakhir = Transaction::lockForUpdate()->orderByDesc('id')->value('kode');
            $nomor    = $terakhir ? ((int) preg_replace('/\D/', '', $terakhir)) + 1 : 1;

            $trx = Transaction::create([
                'kode'           => 'TRX-' . str_pad($nomor, 4, '0', STR_PAD_LEFT),
                'user_id'        => $request->user()->id,
                'tanggal'        => now(),
                'metode'         => $data['metode'],
                'status'         => 'lunas',
                'subtotal'       => $subtotal,
                'diskon_persen'  => $persen,
                'diskon_nominal' => $diskon,
                'total'          => $total,
                'bayar'          => $bayar,
                'kembali'        => $bayar - $total,
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

        return response()->json([
            'ok'        => true,
            'kode'      => $trx->kode,
            'total'     => $trx->total,
            'kembali'   => $trx->kembali,
            'struk_url' => route('pos.struk', $trx),
        ]);
    }

    public function struk(Request $request, Transaction $transaction)
    {
        $user = $request->user();

        // Kasir hanya boleh melihat struk transaksinya sendiri
        abort_unless($user->role !== 'kasir' || $transaction->user_id === $user->id, 403);

        $transaction->load(['items', 'kasir']);

        return view('pos.struk', [
            't'     => $transaction,
            's'     => Setting::semua(),
            'cetak' => $request->boolean('cetak'),
        ]);
    }

    // Metode yang dinyalakan owner di Pengaturan > Payment Gateway
    private function metodeAktif(): array
    {
        $s = Setting::semua();

        return array_values(array_filter(
            Transaction::METODE,
            fn ($m) => ($s[self::METODE_KEY[$m]] ?? '0') === '1'
        ));
    }
}