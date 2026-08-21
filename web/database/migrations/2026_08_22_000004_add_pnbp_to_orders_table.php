<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_pnbp')->default(false)->after('is_invoiced');
            $table->string('pnbp_number')->nullable()->after('is_pnbp');
            $table->text('pnbp_note')->nullable()->after('pnbp_number');
            $table->timestamp('pnbp_completed_at')->nullable()->after('pnbp_note');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_pnbp', 'pnbp_number', 'pnbp_note', 'pnbp_completed_at']);
        });
    }
};
