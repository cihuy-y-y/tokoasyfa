<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public const KATEGORI = ['Skincare', 'Make Up', 'Body Care', 'Hair Care', 'Parfum', 'Lainnya'];

    protected $fillable = [
        'kode', 'nama', 'kategori', 'harga_beli', 'harga_jual',
        'stok', 'stok_minimum', 'gambar',
    ];

    // Stok <= batas minimum dianggap menipis (termasuk yang habis)
    public function scopeMenipis($query)
    {
        return $query->whereColumn('stok', '<=', 'stok_minimum');
    }

    // tersedia | menipis | habis
    public function getStatusAttribute(): string
    {
        if ($this->stok <= 0) return 'habis';
        if ($this->stok <= $this->stok_minimum) return 'menipis';
        return 'tersedia';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'habis'   => 'Habis',
            'menipis' => 'Stok Menipis',
            default   => 'Tersedia',
        };
    }
}
