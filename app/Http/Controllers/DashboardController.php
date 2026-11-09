<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ---- Kartu ringkasan ----
        $hariIni = fn () => Transaction::lunas()->whereDate('tanggal', today());
        $kemarin = fn () => Transaction::lunas()->whereDate('tanggal', today()->subDay());

        $penjualanHariIni = (int) $hariIni()->sum('total');
        $penjualanKemarin = (int) $kemarin()->sum('total');
        $trxHariIni       = (int) $hariIni()->count();
        $trxKemarin       = (int) $kemarin()->count();

        $stats = [
            'total_produk'       => Product::count(),
            'produk_baru'        => Product::where('created_at', '>=', now()->startOfMonth())->count(),
            'stok_menipis'       => Product::menipis()->count(),
            'transaksi_hari_ini' => $trxHariIni,
            'transaksi_selisih'  => $trxHariIni - $trxKemarin,
            'penjualan_hari_ini' => $penjualanHariIni,
            'penjualan_persen'   => $penjualanKemarin > 0
                ? (int) round(($penjualanHariIni - $penjualanKemarin) / $penjualanKemarin * 100)
                : null,   // null = kemarin kosong, tidak ada pembanding
        ];

        // ---- Grafik penjualan sesuai periode ----
        $periode = in_array($request->query('periode'), ['1', '7', '30'], true)
            ? $request->query('periode') : '7';

        $dari = today();   // dipakai juga oleh Produk Terlaris di bawah

        if ($periode === '1') {
            // Per jam, hari ini
            $perJam = Transaction::lunas()
                ->whereDate('tanggal', today())
                ->selectRaw('HOUR(tanggal) as jam, SUM(total) as total')
                ->groupBy('jam')
                ->pluck('total', 'jam');

            $labels = [];
            $data   = [];
            foreach (range(0, 23) as $j) {
                $labels[] = str_pad($j, 2, '0', STR_PAD_LEFT) . ':00';
                $data[]   = (int) ($perJam[$j] ?? 0);
            }
            $judul = 'Grafik Penjualan Hari Ini';
        } else {
            $hari = (int) $periode;
            $dari = today()->subDays($hari - 1);

            $perHari = Transaction::lunas()
                ->whereBetween('tanggal', [$dari->copy()->startOfDay(), today()->endOfDay()])
                ->selectRaw('DATE(tanggal) as hari, SUM(total) as total')
                ->groupBy('hari')
                ->pluck('total', 'hari');

            $labels = [];
            $data   = [];
            foreach (CarbonPeriod::create($dari, today()) as $d) {
                $labels[] = $d->copy()->locale('id')->translatedFormat('j M');
                $data[]   = (int) ($perHari[$d->format('Y-m-d')] ?? 0);
            }
            $judul = "Grafik Penjualan {$hari} Hari Terakhir";
        }

        $grafik = ['labels' => $labels, 'data' => $data, 'judul' => $judul, 'periode' => $periode];

        // ---- Produk terlaris (7 hari terakhir) ----
        $top = TransactionItem::selectRaw('nama_produk, SUM(jumlah) as terjual, MAX(product_id) as product_id')
            ->whereHas('transaction', fn ($q) => $q->lunas()->periode(today()->subDays(6), today()))
            ->groupBy('nama_produk')
            ->orderByDesc('terjual')
            ->limit(5)
            ->get();

        $gambar = Product::whereIn('id', $top->pluck('product_id')->filter())->pluck('gambar', 'id');
        $warna  = ['#bfe3f2', '#f7c9d0', '#f9d5dc', '#fbe3a6', '#d9d3f5'];

        $produkTerlaris = $top->values()->map(fn ($p, $i) => [
            'nama'    => $p->nama_produk,
            'terjual' => (int) $p->terjual,
            'gambar'  => $gambar[$p->product_id] ?? null,
            'warna'   => $warna[$i % count($warna)],
        ])->all();

        return view('admin.dashboard', compact('stats', 'grafik', 'produkTerlaris'));
    }
}