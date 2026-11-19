<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class Transaction extends Model
{
    public const METODE = ['QRIS', 'E-Wallet', 'Tunai'];
    public const STATUS = ['lunas' => 'Lunas', 'menunggu' => 'Menunggu', 'pending' => 'Pending', 'batal' => 'Batal'];

    protected $fillable = [
        'kode', 'user_id', 'tanggal', 'metode', 'status', 'total',
        'subtotal', 'diskon_persen', 'diskon_nominal', 'bayar', 'kembali',
        'ref_id', 'gateway_id', 'qr_string', 'qr_url', 'kadaluarsa_at',
    ];

    protected $casts = ['tanggal' => 'datetime', 'kadaluarsa_at' => 'datetime'];

    public function kasir()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    // Hanya transaksi lunas yang dihitung sebagai pendapatan
    public function scopeLunas($query)
    {
        return $query->where('status', 'lunas');
    }

    public function scopePeriode($query, Carbon $dari, Carbon $sampai)
    {
        return $query->whereBetween('tanggal', [$dari->copy()->startOfDay(), $sampai->copy()->endOfDay()]);
    }

        // Terjemahkan status Midtrans ke status transaksi kita
    public function terapkanStatus(string $s): void
    {
        if (in_array($s, ['settlement', 'capture'], true)) {
            $this->tandaiLunas();
        } elseif (in_array($s, ['expire', 'cancel', 'deny', 'failure'], true)) {
            $this->batalkan();
        }
    }
    
    // Dibatalkan/kedaluwarsa: kembalikan stok yang ditahan
    public function batalkan(): void
    {
        DB::transaction(function () {
            $t = static::with('items')->lockForUpdate()->find($this->id);
            if (! $t || $t->status !== 'menunggu') return;
    
            foreach ($t->items as $i) {
                if ($i->product_id) {
                    Product::where('id', $i->product_id)->increment('stok', $i->jumlah);
                }
            }
            $t->update(['status' => 'batal']);
        });
    }
    
    // Aman dipanggil berulang
    public function tandaiLunas(): void
    {
        DB::transaction(function () {
            $t = static::with('items')->lockForUpdate()->find($this->id);
            if (! $t || $t->status === 'lunas') return;
    
            if ($t->status === 'batal') {   // dibayar setelah sempat batal: ambil stok lagi
                foreach ($t->items as $i) {
                    if ($i->product_id) {
                        Product::where('id', $i->product_id)
                            ->update(['stok' => DB::raw('GREATEST(stok - ' . (int) $i->jumlah . ', 0)')]);
                    }
                }
            }
            $t->update(['status' => 'lunas', 'bayar' => $t->total, 'kembali' => 0]);
        });
    }
}
