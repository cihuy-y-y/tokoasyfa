<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // ====== DATA CONTOH (nanti ganti dengan query database) ======
        $stats = [
            'total_produk'      => 120,
            'produk_baru'       => 2,       // dari bulan lalu
            'stok_menipis'      => 8,
            'transaksi_hari_ini'=> 5,
            'transaksi_selisih' => 2,       // dari kemarin
            'penjualan_hari_ini'=> 1250000,
            'penjualan_persen'  => 15,      // dari kemarin
        ];

        // Penjualan 7 hari terakhir (urut dari 6 hari lalu sampai hari ini)
        $nilaiPenjualan = [650000, 550000, 880000, 1050000, 1450000, 1200000, 1500000];

        $labels = [];
        foreach (range(6, 0) as $i) {
            $labels[] = now()->subDays($i)->locale('id')->translatedFormat('j M');
        }

        $grafik = [
            'labels' => $labels,
            'data'   => $nilaiPenjualan,
        ];

        // gambar: nama file di public/images/produk/ (boleh null)
        $produkTerlaris = [
            ['nama' => 'Wardah Lightening Moisturizer', 'terjual' => 25, 'gambar' => 'wardah.png',    'warna' => '#bfe3f2'],
            ['nama' => 'Maybelline Lipstick',           'terjual' => 18, 'gambar' => 'maybelline.png','warna' => '#f7c9d0'],
            ['nama' => 'Focallure Face Wash',           'terjual' => 14, 'gambar' => 'focallure.png', 'warna' => '#f9d5dc'],
            ['nama' => 'Emina Suncreen',                'terjual' => 14, 'gambar' => 'emina.png',     'warna' => '#fbe3a6'],
            ['nama' => 'Somethinc Serum',               'terjual' => 12, 'gambar' => 'somethinc.png', 'warna' => '#d9d3f5'],
        ];

        return view('admin.dashboard', compact('stats', 'grafik', 'produkTerlaris'));
    }
}
