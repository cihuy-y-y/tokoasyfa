<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class KasirDashboardController extends Controller
{
    public function index(Request $request)
    {
        $uid = $request->user()->id;

        // Ringkasan satu hari, hanya transaksi lunas milik kasir ini
        $ringkas = function (Carbon $hari) use ($uid) {
            $q = fn () => Transaction::lunas()->where('user_id', $uid)->whereDate('tanggal', $hari);

            $total  = (int) $q()->sum('total');
            $jumlah = (int) $q()->count();
            $pcs    = (int) TransactionItem::whereHas('transaction',
                        fn ($t) => $t->lunas()->where('user_id', $uid)->whereDate('tanggal', $hari))->sum('jumlah');

            return ['total' => $total, 'jumlah' => $jumlah, 'pcs' => $pcs,
                    'rata'  => $jumlah ? (int) round($total / $jumlah) : 0];
        };

        $now  = $ringkas(today());
        $prev = $ringkas(today()->subDay());

        $persen = fn ($a, $b) => $b > 0 ? (int) round(($a - $b) / $b * 100) : null;

        $tren = [
            'total'  => $persen($now['total'], $prev['total']),
            'jumlah' => $now['jumlah'] - $prev['jumlah'],          // selisih, bukan persen
            'pcs'    => $persen($now['pcs'], $prev['pcs']),
            'rata'   => $persen($now['rata'], $prev['rata']),
        ];

        // Grafik 7 hari terakhir
        $dari = today()->subDays(6);
        $perHari = Transaction::lunas()->where('user_id', $uid)
            ->whereBetween('tanggal', [$dari->copy()->startOfDay(), today()->endOfDay()])
            ->selectRaw('DATE(tanggal) as hari, SUM(total) as total')
            ->groupBy('hari')->pluck('total', 'hari');

        $labels = [];
        $data   = [];
        foreach (CarbonPeriod::create($dari, today()) as $d) {
            $labels[] = $d->copy()->locale('id')->translatedFormat('D');
            $data[]   = (int) ($perHari[$d->format('Y-m-d')] ?? 0);
        }

        // Transaksi terbaru
        $terbaru = Transaction::where('user_id', $uid)->latest('tanggal')->limit(5)->get();

        // Tips hari ini (berubah tiap hari, atau mengingatkan stok menipis)
        $menipis = Product::menipis()->count();
        $tips = [
            'Pastikan stok barang selalu tersedia agar pelanggan tetap puas!',
            'Sapa pelanggan dengan ramah, senyum kecil membuat mereka kembali lagi.',
            'Tawarkan produk pelengkap, misalnya pelembap saat pelanggan membeli serum.',
            'Periksa kembali jumlah barang dan total sebelum menerima pembayaran.',
            'Rapikan etalase produk agar pelanggan mudah memilih.',
        ];
        $tip = $menipis > 0
            ? "Ada {$menipis} barang yang stoknya menipis. Segera info ke admin untuk restock!"
            : $tips[now()->dayOfYear % count($tips)];

        return view('kasir.dashboard', [
            'now'     => $now,
            'tren'    => $tren,
            'grafik'  => ['labels' => $labels, 'data' => $data],
            'terbaru' => $terbaru,
            'tip'     => $tip,
        ]);
    }
}