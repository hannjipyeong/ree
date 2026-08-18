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
        Schema::create('sub_task_container_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_task_id')->constrained('sub_tasks')->onDelete('cascade');
            $table->foreignId('order_container_id')->constrained('order_containers')->onDelete('cascade');
            $table->string('status')->default('Pending'); // Pending, In, Out
            $table->text('in_note')->nullable();
            $table->string('in_photo_path')->nullable();
            $table->timestamp('in_time')->nullable();
            $table->text('out_note')->nullable();
            $table->string('out_photo_path')->nullable();
            $table->timestamp('out_time')->nullable();
            $table->timestamps();
            
            $table->unique(['sub_task_id', 'order_container_id'], 'sub_task_container_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_task_container_progress');
    }
};
