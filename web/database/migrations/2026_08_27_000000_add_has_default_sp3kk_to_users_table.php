<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'has_default_sp3kk')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('has_default_sp3kk')->default(false)->after('has_default_asuransi');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'has_default_sp3kk')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('has_default_sp3kk');
            });
        }
    }
};
