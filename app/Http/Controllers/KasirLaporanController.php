<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class KasirLaporanController extends Controller
{
    public function index(Request $request)
    {
        $uid = $request->user()->id;

        // Dasar: hanya milik kasir ini dan hanya hari ini (reset otomatis tiap hari)
        $dasar = fn () => Transaction::where('user_id', $uid)->whereDate('tanggal', today());

        $query = $dasar()->with('items.product');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('kode', 'like', "%{$q}%")
                  ->orWhereHas('items', fn ($i) => $i->where('nama_produk', 'like', "%{$q}%"));
            });
        }
        if ($request->filled('metode')) $query->where('metode', $request->metode);
        if ($request->filled('status')) $query->where('status', $request->status);

        $transaksi = $query->latest('tanggal')->latest('id')->paginate(10)->withQueryString();

        return view('kasir.laporan', [
            'transaksi'  => $transaksi,
            'total'      => (int) $dasar()->lunas()->sum('total'),
            'jumlah'     => (int) $dasar()->lunas()->count(),
            'metodeList' => Transaction::METODE,
            'statusList' => Transaction::STATUS,
        ]);
    }
}