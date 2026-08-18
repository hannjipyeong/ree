<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_containers', function (Blueprint $table) {
            $table->string('tkbm_option')->nullable()->after('container_number');
            $table->json('additional_services')->nullable()->after('tkbm_option');
        });

        Schema::table('order_service_changes', function (Blueprint $table) {
            $table->foreignId('order_container_id')->nullable()->after('order_id')->constrained('order_containers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('order_service_changes', function (Blueprint $table) {
            $table->dropForeign(['order_container_id']);
            $table->dropColumn('order_container_id');
        });

        Schema::table('order_containers', function (Blueprint $table) {
            $table->dropColumn(['tkbm_option', 'additional_services']);
        });
    }
};
