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
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('haulage_file_path', 'railing_file_path');
        });

        // Update existing data for Haulage -> Railing
        \Illuminate\Support\Facades\DB::table('sub_tasks')
            ->where('service_type', 'Haulage')
            ->update(['service_type' => 'Railing']);
        
        \Illuminate\Support\Facades\DB::table('users')
            ->where('supir_type', 'Haulage')
            ->update(['supir_type' => 'Railing']);

        // Update existing data for Penumpukan -> Storage
        \Illuminate\Support\Facades\DB::table('sub_tasks')
            ->where('service_type', 'Penumpukan')
            ->update(['service_type' => 'Storage']);
        
        \Illuminate\Support\Facades\DB::table('users')
            ->where('supir_type', 'Penumpukan')
            ->update(['supir_type' => 'Storage']);

        // Also update jenis_kegiatan if there is any 'penumpukan' in orders
        \Illuminate\Support\Facades\DB::table('orders')
            ->where('jenis_kegiatan', 'penumpukan')
            ->update(['jenis_kegiatan' => 'storage']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('railing_file_path', 'haulage_file_path');
        });

        \Illuminate\Support\Facades\DB::table('sub_tasks')
            ->where('service_type', 'Railing')
            ->update(['service_type' => 'Haulage']);
        
        \Illuminate\Support\Facades\DB::table('users')
            ->where('supir_type', 'Railing')
            ->update(['supir_type' => 'Haulage']);

        \Illuminate\Support\Facades\DB::table('sub_tasks')
            ->where('service_type', 'Storage')
            ->update(['service_type' => 'Penumpukan']);
        
        \Illuminate\Support\Facades\DB::table('users')
            ->where('supir_type', 'Storage')
            ->update(['supir_type' => 'Penumpukan']);

        \Illuminate\Support\Facades\DB::table('orders')
            ->where('jenis_kegiatan', 'storage')
            ->update(['jenis_kegiatan' => 'penumpukan']);
    }
};
