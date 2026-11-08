<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;

class NotifikasiService
{
    /**
     * Kembalikan ['items' => daftar notifikasi, 'baru' => jumlah belum dibaca].
     * Sebuah notifikasi dianggap "baru" bila waktunya lebih baru dari notif_dibaca_at user.
     */
    public static function untuk(User $user): array
    {
        $s     = Setting::semua();
        $items = collect();
        // Kasir tidak boleh membuka halaman admin, jadi linknya dimatikan
        $url = fn (string $nama, array $param = []) => $user->role === 'kasir' ? '#' : route($nama, $param);

        // ---- Stok menipis (masih ada sisa, tapi di bawah batas) ----
        if (($s['notif_stok_menipis'] ?? '0') === '1') {
            $menipis = Product::where('stok', '>', 0)->menipis()->orderBy('stok')->get(['nama', 'stok', 'updated_at']);

            if ($menipis->isNotEmpty()) {
                $nama = $menipis->take(3)->pluck('nama')->implode(', ');
                $sisa = $menipis->count() - 3;

                $items->push([
                    'ikon'  => 'stok',
                    'judul' => $menipis->count() . ' barang stok menipis',
                    'pesan' => $nama . ($sisa > 0 ? " dan {$sisa} lainnya" : ''),
                    'url'   => $url('barang.index', ['stok' => 'menipis']),
                    'waktu' => $menipis->max('updated_at'),
                ]);
            }
        }

        // ---- Stok habis ----
        if (($s['notif_stok_habis'] ?? '0') === '1') {
            $habis = Product::where('stok', 0)->orderBy('nama')->get(['nama', 'updated_at']);

            if ($habis->isNotEmpty()) {
                $nama = $habis->take(3)->pluck('nama')->implode(', ');
                $sisa = $habis->count() - 3;

                $items->push([
                    'ikon'  => 'habis',
                    'judul' => $habis->count() . ' barang stok habis',
                    'pesan' => $nama . ($sisa > 0 ? " dan {$sisa} lainnya" : ''),
                    'url'   => $url('barang.index', ['stok' => 'habis']),
                    'waktu' => $habis->max('updated_at'),
                ]);
            }
        }

        // ---- Transaksi baru (3 hari terakhir) ----
        if (($s['notif_transaksi'] ?? '0') === '1') {
            $trx = Transaction::with('kasir')
                ->where('tanggal', '>=', now()->subDays(3))
                ->where('tanggal', '<=', now())
                ->latest('tanggal')
                ->limit(5)
                ->get();

            foreach ($trx as $t) {
                $items->push([
                    'ikon'  => 'transaksi',
                    'judul' => 'Transaksi ' . $t->kode,
                    'pesan' => 'Rp ' . number_format($t->total, 0, ',', '.') . ' via ' . $t->metode
                               . ' (' . ($t->kasir->name ?? 'kasir') . ')',
                    'url'   => $url('laporan.index', ['q' => $t->kode]),
                    'waktu' => $t->tanggal,
                ]);
            }
        }

        // ---- Laporan harian ----
        if (($s['notif_laporan'] ?? '0') === '1') {
            $hariIni = Transaction::lunas()->whereDate('tanggal', today());

            $items->push([
                'ikon'  => 'laporan',
                'judul' => 'Laporan harian',
                'pesan' => (clone $hariIni)->count() . ' transaksi, pendapatan Rp '
                           . number_format((clone $hariIni)->sum('total'), 0, ',', '.') . ' hari ini',
                'url'   => $url('laporan.index'),
                'waktu' => now()->startOfDay(),   // muncul sebagai "baru" lagi tiap hari
            ]);
        }

        // ---- Pendaftar baru (khusus owner) ----
        if ($user->isOwner()) {
            $pending = User::where('status', 'pending');

            if ((clone $pending)->exists()) {
                $items->push([
                    'ikon'  => 'user',
                    'judul' => (clone $pending)->count() . ' pendaftar menunggu persetujuan',
                    'pesan' => 'Tentukan role admin atau kasir',
                    'url'   => route('owner.users', ['status' => 'pending']),
                    'waktu' => (clone $pending)->max('created_at'),
                ]);
            }
        }

        $items = $items->sortByDesc('waktu')->values()->map(function ($n) use ($user) {
            $n['baru']  = ! $user->notif_dibaca_at || $n['waktu']->gt($user->notif_dibaca_at);
            $n['waktu'] = $n['waktu']->copy()->locale('id')->diffForHumans();
            return $n;
        });

        return ['items' => $items, 'baru' => $items->where('baru', true)->count()];
    }
}