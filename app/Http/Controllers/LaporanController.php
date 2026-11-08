<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);

        // ---- Ringkasan + pembanding periode sebelumnya ----
        $hari       = (int) $dari->diffInDays($sampai) + 1;
        $prevSampai = $dari->copy()->subDay();
        $prevDari   = $prevSampai->copy()->subDays($hari - 1);

        $now  = $this->ringkasan($dari, $sampai);
        $prev = $this->ringkasan($prevDari, $prevSampai);

        $tren = [];
        foreach (['pendapatan', 'jumlah', 'pcs', 'rata'] as $k) {
            $tren[$k] = $this->persen($now[$k], $prev[$k]);
        }

        // ---- Grafik penjualan per hari ----
        $perHari = Transaction::lunas()->periode($dari, $sampai)
            ->selectRaw('DATE(tanggal) as hari, SUM(total) as total')
            ->groupBy('hari')
            ->pluck('total', 'hari');

        $labels = [];
        $data   = [];
        foreach (CarbonPeriod::create($dari, $sampai) as $d) {
            $labels[] = $d->copy()->locale('id')->translatedFormat('j M');
            $data[]   = (int) ($perHari[$d->format('Y-m-d')] ?? 0);
        }

        // ---- Metode pembayaran ----
        $warna = [
            'QRIS' => '#e0759a', 'Transfer Bank' => '#b57edc', 'E-Wallet' => '#f5b942',
            'COD'  => '#5bb8f0', 'Tunai' => '#34d399',
        ];
        $metodeRaw   = Transaction::lunas()->periode($dari, $sampai)
            ->selectRaw('metode, COUNT(*) as jml')->groupBy('metode')->pluck('jml', 'metode');
        $totalMetode = (int) $metodeRaw->sum();

        $metode = collect(Transaction::METODE)->map(fn ($m) => [
            'nama'    => $m,
            'jumlah'  => (int) ($metodeRaw[$m] ?? 0),
            'persen'  => $totalMetode ? round(($metodeRaw[$m] ?? 0) / $totalMetode * 100) : 0,
            'warna'   => $warna[$m],
        ])->all();

        // ---- Produk terlaris ----
        $top = TransactionItem::selectRaw('nama_produk, SUM(jumlah) as terjual, MAX(product_id) as product_id')
            ->whereHas('transaction', fn ($q) => $q->lunas()->periode($dari, $sampai))
            ->groupBy('nama_produk')
            ->orderByDesc('terjual')
            ->limit(5)
            ->get();

        $gambarProduk = Product::whereIn('id', $top->pluck('product_id')->filter())->pluck('gambar', 'id');
        $maxTerjual   = max(1, (int) $top->max('terjual'));

        // ---- Daftar transaksi ----
        $transaksi = $this->daftar($request, $dari, $sampai)->paginate(10)->withQueryString();

        $kasirList = User::whereIn('id', Transaction::whereNotNull('user_id')->select('user_id'))
            ->orderBy('name')->get(['id', 'name']);

        return view('laporan.index', [
            'dari'         => $dari,
            'sampai'       => $sampai,
            'now'          => $now,
            'tren'         => $tren,
            'grafik'       => ['labels' => $labels, 'data' => $data],
            'metode'       => $metode,
            'totalMetode'  => $totalMetode,
            'top'          => $top,
            'gambarProduk' => $gambarProduk,
            'maxTerjual'   => $maxTerjual,
            'transaksi'    => $transaksi,
            'kasirList'    => $kasirList,
            'metodeList'   => Transaction::METODE,
            'statusList'   => Transaction::STATUS,
        ]);
    }

    // Download CSV (bisa dibuka di Excel). Filter ikut terbawa bila ada.
    public function export(Request $request)
    {
        [$dari, $sampai] = $this->periode($request);
        $query    = $this->daftar($request, $dari, $sampai);
        $namaFile = 'laporan-penjualan-' . $dari->format('Y-m-d') . '_' . $sampai->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8
            fputcsv($out, ['No. Transaksi', 'Tanggal', 'Nama Barang', 'Jumlah', 'Total', 'Kasir', 'Metode', 'Status'], ';', '"', '\\');

            $pendapatan = 0;
            foreach ($query->cursor() as $t) {
                $barang = $t->items->map(fn ($i) => $i->nama_produk . ' x' . $i->jumlah)->implode(', ');
                if ($t->status === 'lunas') {
                    $pendapatan += $t->total;
                }

                fputcsv($out, [
                    $t->kode,
                    $t->tanggal->format('d/m/Y H:i'),
                    $barang,
                    $t->items->sum('jumlah'),
                    $t->total,
                    $t->kasir->name ?? '-',
                    $t->metode,
                    ucfirst($t->status),
                ], ';', '"', '\\');
            }

            fputcsv($out, ['', '', '', '', '', '', '', ''], ';', '"', '\\');
            fputcsv($out, ['TOTAL PENDAPATAN (hanya Lunas)', '', '', '', $pendapatan, '', '', ''], ';', '"', '\\');
            fclose($out);
        }, $namaFile, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // Hanya owner (lihat routes)
    public function update(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'metode' => ['required', Rule::in(Transaction::METODE)],
            'status' => ['required', Rule::in(array_keys(Transaction::STATUS))],
        ], [
            'metode.required' => 'Metode pembayaran wajib dipilih.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        $transaction->update($data);

        return back()->with('success', "Transaksi {$transaction->kode} berhasil diperbarui.");
    }

    public function destroy(Transaction $transaction)
    {
        $kode = $transaction->kode;
        $transaction->items()->delete();
        $transaction->delete();

        return back()->with('success', "Transaksi {$kode} dihapus.");
    }

    // ---------- Helper ----------

    private function periode(Request $request): array
    {
        $sampai = $this->tanggal($request->input('sampai'), now());
        $dari   = $this->tanggal($request->input('dari'), now()->subDays(6));

        if ($dari->gt($sampai)) {
            [$dari, $sampai] = [$sampai, $dari];
        }
        if ($dari->diffInDays($sampai) > 365) {      // batasi maksimal 1 tahun
            $dari = $sampai->copy()->subDays(365);
        }

        return [$dari, $sampai];
    }

    private function tanggal(?string $nilai, Carbon $default): Carbon
    {
        try {
            return $nilai
                ? Carbon::createFromFormat('Y-m-d', $nilai)->startOfDay()
                : $default->copy()->startOfDay();
        } catch (\Throwable $e) {
            return $default->copy()->startOfDay();
        }
    }

    private function ringkasan(Carbon $dari, Carbon $sampai): array
    {
        $base = fn () => Transaction::lunas()->periode($dari, $sampai);

        $pendapatan = (int) $base()->sum('total');
        $jumlah     = (int) $base()->count();
        $pcs        = (int) TransactionItem::whereHas('transaction', fn ($q) => $q->lunas()->periode($dari, $sampai))->sum('jumlah');

        return [
            'pendapatan' => $pendapatan,
            'jumlah'     => $jumlah,
            'pcs'        => $pcs,
            'rata'       => $jumlah ? (int) round($pendapatan / $jumlah) : 0,
        ];
    }

    // Persentase perubahan; null bila periode sebelumnya kosong
    private function persen(int $sekarang, int $sebelumnya): ?int
    {
        if ($sebelumnya === 0) {
            return null;
        }
        return (int) round(($sekarang - $sebelumnya) / $sebelumnya * 100);
    }

    private function daftar(Request $request, Carbon $dari, Carbon $sampai)
    {
        $query = Transaction::with(['kasir', 'items.product'])->periode($dari, $sampai);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('kode', 'like', "%{$q}%")
                  ->orWhereHas('items', fn ($i) => $i->where('nama_produk', 'like', "%{$q}%"))
                  ->orWhereHas('kasir', fn ($k) => $k->where('name', 'like', "%{$q}%"));
            });
        }
        if ($request->filled('kasir'))  $query->where('user_id', $request->kasir);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('metode')) $query->where('metode', $request->metode);

        return $query->latest('tanggal')->latest('id');
    }
}