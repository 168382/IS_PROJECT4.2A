<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_sequences', function (Blueprint $table) {
            $table->string('type', 5)->primary();
            $table->unsignedBigInteger('last_id');
        });

        foreach (['lost', 'found'] as $type) {
            DB::table('item_sequences')->insert([
                'type' => $type,
                'last_id' => DB::table('item_records')->where('type', $type)->max('item_id') ?? 0,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('item_sequences');
    }
};
