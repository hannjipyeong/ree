<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('sub_tasks', 'in_time')) {
                $table->timestamp('in_time')->nullable()->after('in_photo_path');
            }
            if (!Schema::hasColumn('sub_tasks', 'out_time')) {
                $table->timestamp('out_time')->nullable()->after('out_photo_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_tasks', function (Blueprint $table) {
            $table->dropColumn(['in_time', 'out_time']);
        });
    }
};
