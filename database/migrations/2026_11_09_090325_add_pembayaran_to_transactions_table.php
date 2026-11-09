<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedTinyInteger('diskon_persen')->default(0);
            $table->unsignedBigInteger('diskon_nominal')->default(0);
            $table->unsignedBigInteger('bayar')->default(0);
            $table->unsignedBigInteger('kembali')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'diskon_persen', 'diskon_nominal', 'bayar', 'kembali']);
        });
    }
};