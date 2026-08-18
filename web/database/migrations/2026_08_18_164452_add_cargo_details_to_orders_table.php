<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('jenis_barang')->nullable()->after('payload_type');
            $table->decimal('jumlah_tonase', 10, 2)->nullable()->after('jenis_barang');
            $table->string('nomor_container_cargo')->nullable()->after('jumlah_tonase');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['jenis_barang', 'jumlah_tonase', 'nomor_container_cargo']);
        });
    }
};
