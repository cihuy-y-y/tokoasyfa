<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['W001', 'Wardah Lightening Moisturizer', 'Skincare', 18000, 25000, 25],
            ['M002', 'Maybelline Lipstick',           'Make Up',  32000, 55000, 18],
            ['E003', 'Emina Suncreen',                'Skincare', 28000, 45000, 12],
            ['S004', 'Somethinc Serum',               'Skincare', 65000, 95000, 8],
            ['M005', 'Make Over Powder',              'Make Up',  45000, 75000, 30],
            ['S006', 'Scarlett Toner',                'Skincare', 40000, 65000, 20],
            ['W007', 'Wardah Mascara',                'Make Up',  35000, 55000, 15],
            ['C008', 'Cetaphil Face Wash',            'Skincare', 50000, 80000, 6],
        ];

        foreach ($items as [$kode, $nama, $kategori, $beli, $jual, $stok]) {
            Product::updateOrCreate(['kode' => $kode], [
                'nama'         => $nama,
                'kategori'     => $kategori,
                'harga_beli'   => $beli,
                'harga_jual'   => $jual,
                'stok'         => $stok,
                'stok_minimum' => 10,
            ]);
        }
    }
}