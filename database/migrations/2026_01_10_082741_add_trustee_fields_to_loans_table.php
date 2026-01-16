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
        Schema::table('loans', function (Blueprint $table) {
            $table->bigInteger('trustee1_member_id')->unsigned()->nullable()->after('borrower_id');
            $table->bigInteger('trustee2_member_id')->unsigned()->nullable()->after('trustee1_member_id');
            
            $table->foreign('trustee1_member_id')->references('id')->on('members')->onDelete('set null');
            $table->foreign('trustee2_member_id')->references('id')->on('members')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['trustee1_member_id']);
            $table->dropForeign(['trustee2_member_id']);
            $table->dropColumn(['trustee1_member_id', 'trustee2_member_id']);
        });
    }
};
