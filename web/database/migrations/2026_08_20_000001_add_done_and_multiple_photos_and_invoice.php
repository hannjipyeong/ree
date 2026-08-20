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
        // 1. Add invoice tracking columns to orders table
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'is_invoiced')) {
                $table->boolean('is_invoiced')->default(false)->after('status');
            }
            if (!Schema::hasColumn('orders', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->after('is_invoiced');
            }
            if (!Schema::hasColumn('orders', 'invoiced_at')) {
                $table->timestamp('invoiced_at')->nullable()->after('invoice_number');
            }
        });

        // 2. Add multiple photos and done details to sub_tasks table
        Schema::table('sub_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('sub_tasks', 'in_photos')) {
                $table->json('in_photos')->nullable()->after('in_photo_path');
            }
            if (!Schema::hasColumn('sub_tasks', 'out_photos')) {
                $table->json('out_photos')->nullable()->after('out_photo_path');
            }
            if (!Schema::hasColumn('sub_tasks', 'done_note')) {
                $table->text('done_note')->nullable()->after('out_photos');
            }
            if (!Schema::hasColumn('sub_tasks', 'done_photo_path')) {
                $table->string('done_photo_path')->nullable()->after('done_note');
            }
            if (!Schema::hasColumn('sub_tasks', 'done_photos')) {
                $table->json('done_photos')->nullable()->after('done_photo_path');
            }
            if (!Schema::hasColumn('sub_tasks', 'done_time')) {
                $table->timestamp('done_time')->nullable()->after('done_photos');
            }
        });

        // 3. Add multiple photos and done details to sub_task_container_progress table
        Schema::table('sub_task_container_progress', function (Blueprint $table) {
            if (!Schema::hasColumn('sub_task_container_progress', 'in_photos')) {
                $table->json('in_photos')->nullable()->after('in_photo_path');
            }
            if (!Schema::hasColumn('sub_task_container_progress', 'out_photos')) {
                $table->json('out_photos')->nullable()->after('out_photo_path');
            }
            if (!Schema::hasColumn('sub_task_container_progress', 'done_note')) {
                $table->text('done_note')->nullable()->after('out_time');
            }
            if (!Schema::hasColumn('sub_task_container_progress', 'done_photo_path')) {
                $table->string('done_photo_path')->nullable()->after('done_note');
            }
            if (!Schema::hasColumn('sub_task_container_progress', 'done_photos')) {
                $table->json('done_photos')->nullable()->after('done_photo_path');
            }
            if (!Schema::hasColumn('sub_task_container_progress', 'done_time')) {
                $table->timestamp('done_time')->nullable()->after('done_photos');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_invoiced', 'invoice_number', 'invoiced_at']);
        });

        Schema::table('sub_tasks', function (Blueprint $table) {
            $table->dropColumn(['in_photos', 'out_photos', 'done_note', 'done_photo_path', 'done_photos', 'done_time']);
        });

        Schema::table('sub_task_container_progress', function (Blueprint $table) {
            $table->dropColumn(['in_photos', 'out_photos', 'done_note', 'done_photo_path', 'done_photos', 'done_time']);
        });
    }
};
