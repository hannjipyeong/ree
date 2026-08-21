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
        Schema::table('sub_task_container_progress', function (Blueprint $table) {
            $table->boolean('is_invoiced')->default(false)->after('status');
            $table->string('invoice_number')->nullable()->after('is_invoiced');
            $table->timestamp('invoiced_at')->nullable()->after('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_task_container_progress', function (Blueprint $table) {
            $table->dropColumn(['is_invoiced', 'invoice_number', 'invoiced_at']);
        });
    }
};
