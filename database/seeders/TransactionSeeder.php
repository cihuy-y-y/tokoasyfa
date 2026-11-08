<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        if ($products->isEmpty()) {
            $this->command->warn('Belum ada produk. Jalankan ProductSeeder dulu.');
            return;
        }

        // Bersihkan data contoh lama
        TransactionItem::query()->delete();
        Transaction::query()->delete();

        $kasirIds = User::whereIn('role', ['kasir', 'admin', 'owner'])
            ->where('status', 'approved')->pluck('id')->all();

        // Bobot metode pembayaran (mirip mockup: QRIS paling banyak)
        $bobot = ['QRIS' => 46, 'Transfer Bank' => 27, 'E-Wallet' => 15, 'COD' => 8, 'Tunai' => 4];
        $pool  = [];
        foreach ($bobot as $metode => $b) {
            $pool = array_merge($pool, array_fill(0, $b, $metode));
        }

        // 70 transaksi acak dalam 14 hari terakhir (supaya ada pembanding periode sebelumnya)
        $waktu = [];
        for ($i = 0; $i < 70; $i++) {
            $t = now()->subDays(rand(0, 13))->setTime(rand(8, 20), rand(0, 59));
            if ($t->gt(now())) {
                $t = now()->subMinutes(rand(1, 30));
            }
            $waktu[] = $t;
        }
        usort($waktu, fn ($a, $b) => $a <=> $b);

        foreach ($waktu as $no => $t) {
            $r = rand(1, 100);
            $status = $r <= 90 ? 'lunas' : ($r <= 96 ? 'pending' : 'batal');

            $trx = Transaction::create([
                'kode'    => 'TRX-' . str_pad($no + 1, 4, '0', STR_PAD_LEFT),
                'user_id' => $kasirIds ? $kasirIds[array_rand($kasirIds)] : null,
                'tanggal' => $t,
                'metode'  => $pool[array_rand($pool)],
                'status'  => $status,
                'total'   => 0,
            ]);

            $total = 0;
            foreach ($products->random(rand(1, min(3, $products->count()))) as $p) {
                $qty = rand(1, 3);
                $sub = $p->harga_jual * $qty;
                $total += $sub;

                TransactionItem::create([
                    'transaction_id' => $trx->id,
                    'product_id'     => $p->id,
                    'nama_produk'    => $p->nama,
                    'harga'          => $p->harga_jual,
                    'jumlah'         => $qty,
                    'subtotal'       => $sub,
                ]);
            }

            $trx->update(['total' => $total]);
        }
    }
}