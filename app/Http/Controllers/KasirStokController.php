<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class KasirStokController extends Controller
{
    public function index(Request $request)
    {
        $kategoriList = Product::KATEGORI;

        // Kategori bisa lebih dari satu (checkbox), tab hanya memilih satu
        $dipilih = array_values(array_intersect($kategoriList, (array) $request->query('kategori', [])));

        $query = Product::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('nama', 'like', "%{$q}%")->orWhere('kode', 'like', "%{$q}%");
            });
        }

        if ($dipilih) {
            $query->whereIn('kategori', $dipilih);
        }

        match ($request->query('urut')) {
            'nama'   => $query->orderBy('nama'),
            'stok'   => $query->orderBy('stok'),
            'harga'  => $query->orderByDesc('harga_jual'),
            default  => $query->latest('id'),
        };

        $products = $query->paginate(10)->withQueryString();

        // Ringkasan seluruh barang (tidak terpengaruh filter)
        $total   = Product::count();
        $habis   = Product::where('stok', 0)->count();
        $menipis = Product::where('stok', '>', 0)->menipis()->count();

        $summary = [
            'total'   => $total,
            'menipis' => $menipis,
            'habis'   => $habis,
            'aman'    => $total - $menipis - $habis,
        ];

        $menipisList = Product::menipis()->orderBy('stok')->limit(5)->get();

        return view('kasir.stok', compact('products', 'summary', 'kategoriList', 'dipilih', 'menipisList'));
    }
}