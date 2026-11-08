<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();                       // TRX-0001
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // kasir
            $table->dateTime('tanggal');
            $table->string('metode');                               // QRIS, Tunai, dst.
            $table->string('status')->default('lunas');             // lunas | pending | batal
            $table->unsignedBigInteger('total')->default(0);
            $table->timestamps();

            $table->index('tanggal');
        });

        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama_produk');                          // disalin, supaya laporan lama tetap utuh
            $table->unsignedBigInteger('harga');
            $table->unsignedInteger('jumlah');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
    }
};
