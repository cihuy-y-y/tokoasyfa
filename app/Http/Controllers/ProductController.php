<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('nama', 'like', "%{$q}%")
                  ->orWhere('kode', 'like', "%{$q}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        match ($request->stok) {
            'tersedia' => $query->whereColumn('stok', '>', 'stok_minimum'),
            'menipis'  => $query->where('stok', '>', 0)->whereColumn('stok', '<=', 'stok_minimum'),
            'habis'    => $query->where('stok', 0),
            default    => null,
        };

        $products = $query->orderBy('nama')->paginate(10)->withQueryString();

        // Ringkasan (dari seluruh barang, tidak terpengaruh filter)
        $totalProduk = Product::count();
        $habis       = Product::where('stok', 0)->count();
        $menipis     = Product::where('stok', '>', 0)->menipis()->count();
        $summary = [
            'total'      => $totalProduk,
            'menipis'    => $menipis,
            'habis'      => $habis,
            'aman'       => $totalProduk - $menipis - $habis,
            'total_stok' => (int) Product::sum('stok'),
        ];

        $kategoriList = Product::KATEGORI;

        return view('barang.index', compact('products', 'summary', 'kategoriList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages(), $this->attributes());
        unset($data['gambar']);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $this->simpanGambar($request->file('gambar'));
        }

        $product = Product::create($data);

        // Kode otomatis jika dikosongkan
        if (! $product->kode) {
            $product->update(['kode' => 'BRG' . str_pad($product->id, 4, '0', STR_PAD_LEFT)]);
        }

        return redirect()->route('barang.index')->with('success', "Barang \"{$product->nama}\" berhasil ditambahkan.");
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate($this->rules($product), $this->messages(), $this->attributes());
        unset($data['gambar']);

        if ($request->hasFile('gambar')) {
            $this->hapusGambar($product->gambar);
            $data['gambar'] = $this->simpanGambar($request->file('gambar'));
        }

        if (empty($data['kode'])) {
            $data['kode'] = $product->kode ?: 'BRG' . str_pad($product->id, 4, '0', STR_PAD_LEFT);
        }

        $product->update($data);

        return back()->with('success', "Barang \"{$product->nama}\" berhasil diperbarui.");
    }

    public function destroy(Product $product)
    {
        $this->hapusGambar($product->gambar);
        $product->delete();

        return back()->with('success', "Barang \"{$product->nama}\" dihapus.");
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer', 'exists:products,id'],
        ], [
            'ids.required' => 'Pilih barang yang ingin dihapus.',
        ]);

        $products = Product::whereIn('id', $request->ids)->get();
        foreach ($products as $p) {
            $this->hapusGambar($p->gambar);
            $p->delete();
        }

        return back()->with('success', $products->count() . ' barang berhasil dihapus.');
    }

    // ---------- Helper ----------

    private function rules(?Product $product = null): array
    {
        return [
            'kode'         => ['nullable', 'string', 'max:50', Rule::unique('products', 'kode')->ignore($product?->id)],
            'nama'         => ['required', 'string', 'max:255'],
            'kategori'     => ['required', Rule::in(Product::KATEGORI)],
            'harga_beli'   => ['required', 'integer', 'min:0'],
            'harga_jual'   => ['required', 'integer', 'min:0', 'gte:harga_beli'],
            'stok'         => ['required', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'gambar'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'integer'  => ':attribute harus berupa angka.',
            'min'      => ':attribute tidak boleh kurang dari :min.',
            'max'      => ':attribute terlalu panjang / terlalu besar (maks :max).',
            'unique'   => ':attribute sudah dipakai barang lain.',
            'in'       => ':attribute tidak valid.',
            'image'    => ':attribute harus berupa gambar.',
            'mimes'    => ':attribute harus berformat jpg, jpeg, png, atau webp.',
            'gte'      => 'Harga jual tidak boleh lebih kecil dari harga beli.',
        ];
    }

    private function attributes(): array
    {
        return [
            'kode'         => 'Kode barang',
            'nama'         => 'Nama produk',
            'kategori'     => 'Kategori',
            'harga_beli'   => 'Harga beli',
            'harga_jual'   => 'Harga jual',
            'stok'         => 'Stok',
            'stok_minimum' => 'Batas stok menipis',
            'gambar'       => 'Gambar',
        ];
    }

    private function simpanGambar(UploadedFile $file): string
    {
        $nama = Str::random(20) . '.' . $file->extension();
        $file->move(public_path('images/produk'), $nama);

        return $nama;
    }

    private function hapusGambar(?string $nama): void
    {
        if ($nama) {
            File::delete(public_path('images/produk/' . $nama));
        }
    }
}
