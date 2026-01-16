<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove unique constraint from leaders table
        Schema::table('leaders', function (Blueprint $table) {
            $table->dropUnique(['position', 'tenant_id']);
        });

        // Add secretary and chairman selection fields to loans table
        Schema::table('loans', function (Blueprint $table) {
            $table->bigInteger('secretary_leader_id')->unsigned()->nullable()->after('trustee2_member_id');
            $table->bigInteger('chairman_leader_id')->unsigned()->nullable()->after('secretary_leader_id');
            
            $table->foreign('secretary_leader_id')->references('id')->on('leaders')->onDelete('set null');
            $table->foreign('chairman_leader_id')->references('id')->on('leaders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove secretary and chairman fields from loans table
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['secretary_leader_id']);
            $table->dropForeign(['chairman_leader_id']);
            $table->dropColumn(['secretary_leader_id', 'chairman_leader_id']);
        });

        // Re-add unique constraint to leaders table
        Schema::table('leaders', function (Blueprint $table) {
            $table->unique(['position', 'tenant_id']);
        });
    }
};
