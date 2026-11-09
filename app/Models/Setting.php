<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
        protected $fillable = ['key', 'value'];

    public const TEMA = [
        'pink'   => ['label' => 'Pink',   'main' => '#d0668a', 'dark' => '#bb5578', 'soft' => '#fce4ec'],
        'mawar'  => ['label' => 'Mawar',  'main' => '#e11d48', 'dark' => '#be123c', 'soft' => '#ffe4ea'],
        'ungu'   => ['label' => 'Ungu',   'main' => '#8b5cf6', 'dark' => '#7c3aed', 'soft' => '#ede9fe'],
        'oranye' => ['label' => 'Oranye', 'main' => '#ea8a1e', 'dark' => '#d97706', 'soft' => '#fef0d8'],
        'hijau'  => ['label' => 'Hijau',  'main' => '#10b981', 'dark' => '#059669', 'soft' => '#d9f5e6'],
    ];

    // Nilai bawaan bila belum pernah disimpan
    public const DEFAULTS = [
        'nama_toko'       => 'Toko Kosmetik Asya',
        'alamat'          => "Jl. Raya Pasar Baru No. 12\nPadang Pariaman, Sumatera Barat",
        'telepon'         => '0812 3456 7890',
        'email'           => 'asyakosmetik@gmail.com',
        'jam_operasional' => '08.00 - 21.00 WIB',
        'tentang'         => 'Toko Kosmetik Asya menyediakan berbagai produk kecantikan dan perawatan diri berkualitas, mulai dari skincare, makeup, hingga body care dengan harga terjangkau dan produk original.',

        'logo'   => null,
        'banner' => null,

        'notif_transaksi'       => '1',
        'notif_stok_menipis'    => '1',
        'notif_stok_habis'      => '1',
        'notif_laporan'         => '0',

        'tema_warna'    => 'pink',
        'mode_tampilan' => 'terang',

        'metode_qris'     => '1',
        'metode_transfer' => '1',
        'metode_ewallet'  => '1',
        'metode_tunai'    => '1',
        'bank_nama'       => null,
        'bank_rekening'   => null,
        'bank_atas_nama'  => null,
        'ewallet_nomor'   => null,

        'last_backup_at'  => null,
    ];

    private static ?array $memo = null;

    // Semua pengaturan (bawaan + yang tersimpan), dibaca sekali per request
    public static function semua(): array
    {
        if (self::$memo === null) {
            self::$memo = array_merge(self::DEFAULTS, static::query()->pluck('value', 'key')->all());
        }

        return self::$memo;
    }

    public static function ambil(string $key, $default = null)
    {
        return self::semua()[$key] ?? $default;
    }

    public static function simpan(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        self::$memo = null;
    }

    public static function simpanBanyak(array $data): void
    {
        foreach ($data as $key => $value) {
            static::simpan($key, $value);
        }
    }

    public static function tema(): array
    {
        return self::TEMA[self::ambil('tema_warna')] ?? self::TEMA['pink'];
    }
}
