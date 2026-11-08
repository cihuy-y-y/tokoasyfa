<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class Transaction extends Model
{
    public const METODE = ['QRIS', 'Transfer Bank', 'E-Wallet', 'COD', 'Tunai'];
    public const STATUS = ['lunas' => 'Lunas', 'pending' => 'Pending', 'batal' => 'Batal'];

    protected $fillable = ['kode', 'user_id', 'tanggal', 'metode', 'status', 'total'];

    protected $casts = ['tanggal' => 'datetime'];

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
}
