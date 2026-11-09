<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class NotifikasiService
{
    /**
     * $penuh = false : ringkasan untuk panel lonceng.
     * $penuh = true  : daftar lengkap untuk halaman notifikasi.
     */
    public static function untuk(User $user, bool $penuh = false): array
    {
        $s     = Setting::semua();
        $items = collect();
        $kasir = $user->role === 'kasir';

        // Link menyesuaikan role (kasir tidak boleh membuka halaman admin)
        $url = function (string $admin, string $kasirRoute, array $param = []) use ($kasir) {
            if ($kasir) {
                return Route::has($kasirRoute) ? route($kasirRoute) : '#';
            }
            return route($admin, $param);
        };

        // ---- Stok menipis ----
        if (($s['notif_stok_menipis'] ?? '0') === '1') {
            $menipis = Product::where('stok', '>', 0)->menipis()->orderBy('stok')->get(['nama', 'stok', 'updated_at']);

            if ($penuh) {
                foreach ($menipis as $p) {
                    $items->push([
                        'ikon'  => 'stok', 'jenis' => 'stok',
                        'judul' => 'Stok menipis: ' . $p->nama,
                        'pesan' => 'Sisa ' . $p->stok . ' pcs, segera tambah stok.',
                        'url'   => $url('barang.index', 'kasir.stok', ['q' => $p->nama]),
                        'waktu' => $p->updated_at,
                    ]);
                }
            } elseif ($menipis->isNotEmpty()) {
                $sisa = $menipis->count() - 3;
                $items->push([
                    'ikon'  => 'stok', 'jenis' => 'stok',
                    'judul' => $menipis->count() . ' barang stok menipis',
                    'pesan' => $menipis->take(3)->pluck('nama')->implode(', ') . ($sisa > 0 ? " dan {$sisa} lainnya" : ''),
                    'url'   => $url('barang.index', 'kasir.stok', ['stok' => 'menipis']),
                    'waktu' => $menipis->max('updated_at'),
                ]);
            }
        }

        // ---- Stok habis ----
        if (($s['notif_stok_habis'] ?? '0') === '1') {
            $habis = Product::where('stok', 0)->orderBy('nama')->get(['nama', 'updated_at']);

            if ($penuh) {
                foreach ($habis as $p) {
                    $items->push([
                        'ikon'  => 'habis', 'jenis' => 'stok',
                        'judul' => 'Stok habis: ' . $p->nama,
                        'pesan' => 'Produk ini tidak bisa dijual sampai stok ditambah.',
                        'url'   => $url('barang.index', 'kasir.stok', ['q' => $p->nama]),
                        'waktu' => $p->updated_at,
                    ]);
                }
            } elseif ($habis->isNotEmpty()) {
                $sisa = $habis->count() - 3;
                $items->push([
                    'ikon'  => 'habis', 'jenis' => 'stok',
                    'judul' => $habis->count() . ' barang stok habis',
                    'pesan' => $habis->take(3)->pluck('nama')->implode(', ') . ($sisa > 0 ? " dan {$sisa} lainnya" : ''),
                    'url'   => $url('barang.index', 'kasir.stok', ['stok' => 'habis']),
                    'waktu' => $habis->max('updated_at'),
                ]);
            }
        }

        // ---- Transaksi baru (kasir hanya miliknya sendiri) ----
        if (($s['notif_transaksi'] ?? '0') === '1') {
            $trx = Transaction::with('kasir')
                ->where('tanggal', '>=', now()->subDays($penuh ? 30 : 3))
                ->where('tanggal', '<=', now())
                ->when($kasir, fn ($q) => $q->where('user_id', $user->id))
                ->latest('tanggal')
                ->limit($penuh ? 50 : 5)
                ->get();

            foreach ($trx as $t) {
                $items->push([
                    'ikon'  => 'transaksi', 'jenis' => 'transaksi',
                    'judul' => 'Transaksi ' . $t->kode,
                    'pesan' => 'Rp ' . number_format($t->total, 0, ',', '.') . ' via ' . $t->metode
                               . ' (' . ($t->kasir->name ?? 'kasir') . ')',
                    'url'   => $url('laporan.index', 'kasir.laporan', ['q' => $t->kode]),
                    'waktu' => $t->tanggal,
                ]);
            }
        }

        // ---- Laporan harian ----
        if (($s['notif_laporan'] ?? '0') === '1') {
            $hariIni = Transaction::lunas()->whereDate('tanggal', today())
                ->when($kasir, fn ($q) => $q->where('user_id', $user->id));

            $items->push([
                'ikon'  => 'laporan', 'jenis' => 'laporan',
                'judul' => 'Laporan harian',
                'pesan' => (clone $hariIni)->count() . ' transaksi, pendapatan Rp '
                           . number_format((clone $hariIni)->sum('total'), 0, ',', '.') . ' hari ini',
                'url'   => $url('laporan.index', 'kasir.laporan'),
                'waktu' => now()->startOfDay(),
            ]);
        }

        // ---- Pendaftar baru (khusus owner) ----
        if ($user->isOwner()) {
            $pending = User::where('status', 'pending');

            if ((clone $pending)->exists()) {
                $items->push([
                    'ikon'  => 'user', 'jenis' => 'user',
                    'judul' => (clone $pending)->count() . ' pendaftar menunggu persetujuan',
                    'pesan' => 'Tentukan role admin atau kasir',
                    'url'   => route('owner.users', ['status' => 'pending']),
                    'waktu' => (clone $pending)->max('created_at'),
                ]);
            }
        }

        $items = $items->sortByDesc('waktu')->values()->map(function ($n) use ($user) {
            $n['baru']        = ! $user->notif_dibaca_at || $n['waktu']->gt($user->notif_dibaca_at);
            $n['waktu_penuh'] = $n['waktu']->copy()->locale('id')->translatedFormat('j M Y, H:i');
            $n['waktu']       = $n['waktu']->copy()->locale('id')->diffForHumans();
            return $n;
        });

        return ['items' => $items, 'baru' => $items->where('baru', true)->count()];
    }
}