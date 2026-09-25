<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->text('collection_verification_details')->nullable()->after('proof_image_path');
            $table->text('collection_notes')->nullable()->after('collection_verification_details');
            $table->timestamp('collected_at')->nullable()->after('status');
            $table->foreignId('collected_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('collected_at');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropConstrainedForeignId('collected_by_user_id');
            $table->dropColumn(['collection_verification_details', 'collection_notes', 'collected_at']);
        });
    }
};
