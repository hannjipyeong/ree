<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('jumlah_barang')->nullable()->after('jenis_barang');
            $table->string('nomor_bl')->nullable()->after('jumlah_tonase');
            $table->string('vessel')->nullable()->after('nomor_bl');
            $table->string('voyage')->nullable()->after('vessel');
            $table->string('no_surat_jalan')->nullable()->after('voyage');
            $table->string('no_bp')->nullable()->after('no_surat_jalan');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'jumlah_barang',
                'nomor_bl',
                'vessel',
                'voyage',
                'no_surat_jalan',
                'no_bp',
            ]);
        });
    }
};
