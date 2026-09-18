<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nlp_dataset', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->text('description');
            $table->string('category_name');
            $table->string('color')->nullable();
            $table->string('brand')->nullable();
            $table->string('location')->nullable();
            $table->enum('item_type', ['lost', 'found']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nlp_dataset');
    }
};
