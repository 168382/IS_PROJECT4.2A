
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nlp_dataset', function (Blueprint $table) {
            $table->unsignedBigInteger('source_item_id')->nullable()->after('id');

            $table->unique(
                ['item_type', 'source_item_id'],
                'nlp_dataset_source_item_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('nlp_dataset', function (Blueprint $table) {
            $table->dropUnique('nlp_dataset_source_item_unique');
            $table->dropColumn('source_item_id');
        });
    }
};
