<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->nullable()->unique();      // SKU
            $table->string('nama');
            $table->string('kategori')->default('Lainnya');
            $table->unsignedBigInteger('harga_beli')->default(0);
            $table->unsignedBigInteger('harga_jual')->default(0);
            $table->unsignedInteger('stok')->default(0);
            $table->unsignedInteger('stok_minimum')->default(10); // batas "stok menipis"
            $table->string('gambar')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
