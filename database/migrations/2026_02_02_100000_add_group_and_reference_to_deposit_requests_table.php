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
        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->string('user_transaction_id')->nullable()->after('requirements');
            $table->string('user_reference')->nullable()->after('user_transaction_id');
            $table->string('deposit_request_group_id', 36)->nullable()->after('user_reference');
        });
        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->index('deposit_request_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->dropIndex(['deposit_request_group_id']);
        });
        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->dropColumn(['user_transaction_id', 'user_reference', 'deposit_request_group_id']);
        });
    }
};
