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
        // Get the actual foreign key constraint name
        $constraintName = DB::selectOne("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'leaders' 
            AND COLUMN_NAME = 'member_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        if ($constraintName) {
            $constraintName = $constraintName->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE `leaders` DROP FOREIGN KEY `{$constraintName}`");
        }
        
        // Make member_id nullable
        DB::statement('ALTER TABLE `leaders` MODIFY `member_id` BIGINT UNSIGNED NULL');
        
        // Re-add the foreign key with set null on delete
        DB::statement('ALTER TABLE `leaders` ADD CONSTRAINT `leaders_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key constraint
        DB::statement('ALTER TABLE `leaders` DROP FOREIGN KEY `leaders_member_id_foreign`');
        
        // Make member_id not nullable again
        DB::statement('ALTER TABLE `leaders` MODIFY `member_id` BIGINT UNSIGNED NOT NULL');
        
        // Re-add the foreign key with cascade on delete
        DB::statement('ALTER TABLE `leaders` ADD CONSTRAINT `leaders_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE');
    }
};
