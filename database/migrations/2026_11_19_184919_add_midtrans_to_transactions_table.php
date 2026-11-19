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
    Schema::table('transactions', function (Blueprint $table) {
        $table->string('ref_id')->nullable()->unique();   // order_id di Midtrans
        $table->string('gateway_id')->nullable();
        $table->text('qr_string')->nullable();
        $table->text('qr_url')->nullable();
        $table->dateTime('kadaluarsa_at')->nullable();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::table('transactions', function (Blueprint $table) {
        $table->dropColumn(['ref_id', 'gateway_id', 'qr_string', 'qr_url', 'kadaluarsa_at']);
    });
    }
};
