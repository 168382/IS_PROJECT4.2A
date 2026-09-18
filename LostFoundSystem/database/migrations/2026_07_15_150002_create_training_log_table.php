<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('records_count')->default(0);
            $table->decimal('accuracy', 5, 2)->nullable();
            $table->string('model_path')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['started', 'completed', 'failed'])->default('started');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_log');
    }
};
