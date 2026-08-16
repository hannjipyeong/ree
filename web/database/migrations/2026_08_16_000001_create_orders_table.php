<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('source')->default('ALL IN'); // ALL IN, Koperasi, PBM Lain
            $table->date('tanggal_order');
            $table->string('nama_pt');
            $table->string('nama_pbm')->default('PT. ABC');
            $table->string('no_telp');
            $table->string('wilayah'); // Selatan, Eximen, Utara
            $table->string('lokasi_fasilitas'); // TPFT, CFS, loss cargo, gudang, tps
            $table->string('jenis_kegiatan'); // cek fisik, striping / staffing, penumpukan
            $table->string('payload_type')->default('Container'); // Container, Cargo
            $table->string('cargo_file_path')->nullable();
            $table->string('haulage_file_path')->nullable();
            $table->string('tbkm_option')->nullable(); // Man Power, Man Power + Forklift
            $table->string('status')->default('Submitted'); // Submitted, In Progress, Completed, Cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
