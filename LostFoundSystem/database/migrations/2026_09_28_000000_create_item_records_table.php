<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_records', function (Blueprint $table) {
            $table->string('type', 5);
            $table->unsignedBigInteger('item_id');
            $table->json('attributes');
            $table->binary('image_data')->nullable();
            $table->string('image_mime', 32)->nullable();
            $table->primary(['type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_records');
    }
};
