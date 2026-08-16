<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_number')->unique();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('service_type'); // Haulage, LOLO, Penumpukan, TBKM
            $table->foreignId('supir_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('Masuk'); // Masuk, In, Out, Done
            $table->text('in_note')->nullable();
            $table->string('in_photo_path')->nullable();
            $table->text('out_note')->nullable();
            $table->string('out_photo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_tasks');
    }
};
