<?php

namespace App\Http\Controllers;

use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $semua = NotifikasiService::untuk($request->user(), true)['items'];

        $jenis = $request->query('jenis', 'semua');

        $hitung = [
            'semua'     => $semua->count(),
            'baru'      => $semua->where('baru', true)->count(),
            'stok'      => $semua->where('jenis', 'stok')->count(),
            'transaksi' => $semua->where('jenis', 'transaksi')->count(),
            'laporan'   => $semua->where('jenis', 'laporan')->count(),
            'user'      => $semua->where('jenis', 'user')->count(),
        ];

        $daftar = (match (true) {
            $jenis === 'baru'                                      => $semua->where('baru', true),
            in_array($jenis, ['stok', 'transaksi', 'laporan', 'user'], true) => $semua->where('jenis', $jenis),
            default                                                => $semua,
        })->values();

        $perHalaman = 15;
        $halaman    = max(1, (int) $request->query('page', 1));

        $items = new LengthAwarePaginator(
            $daftar->forPage($halaman, $perHalaman)->values(),
            $daftar->count(),
            $perHalaman,
            $halaman,
            ['path' => route('notifikasi.index'), 'query' => $request->except('page')]
        );

        return view('notifikasi.index', compact('items', 'hitung', 'jenis'));
    }

    public function baca(Request $request)
    {
        $request->user()->forceFill(['notif_dibaca_at' => now()])->save();

        return back();
    }
}