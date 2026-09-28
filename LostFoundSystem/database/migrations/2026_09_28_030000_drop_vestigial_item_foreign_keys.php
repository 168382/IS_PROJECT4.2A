<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lost/found items are actually stored in the `item_records` table (see
 * ItemRecordRepository), not in the `found_items`/`lost_items` tables. Those
 * tables are vestigial and always empty, but `claims` and `item_matches`
 * still had foreign keys pointing at them. That went unnoticed while claims
 * and matches were stored via the JSON flat-file service (no FK
 * enforcement); now that they are real Eloquent-backed tables, the stale
 * constraints reject every insert. Drop them since `found_item_id`/
 * `lost_item_id` here reference `item_records` item IDs, not rows in
 * `found_items`/`lost_items`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropForeign(['found_item_id']);
        });

        Schema::table('item_matches', function (Blueprint $table) {
            $table->dropForeign(['lost_item_id']);
            $table->dropForeign(['found_item_id']);
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->foreign('found_item_id')->references('id')->on('found_items')->cascadeOnDelete();
        });

        Schema::table('item_matches', function (Blueprint $table) {
            $table->foreign('lost_item_id')->references('id')->on('lost_items')->cascadeOnDelete();
            $table->foreign('found_item_id')->references('id')->on('found_items')->cascadeOnDelete();
        });
    }
};
