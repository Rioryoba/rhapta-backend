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
        if (!Schema::hasTable('tasks')) {
            // Table doesn't exist, nothing to rename
            return;
        }
        
        // Drop foreign key constraints BEFORE renaming (constraint names include table name)
        // Use raw SQL to drop foreign key since Doctrine DBAL may not be available
        $connection = Schema::getConnection();
        $dbName = $connection->getDatabaseName();
        $tableName = 'tasks';
        
        // Get foreign key constraint names from information_schema
        $foreignKeys = $connection->select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = ?
            AND COLUMN_NAME = 'project_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$dbName, $tableName]);
        
        Schema::table('tasks', function (Blueprint $table) use ($foreignKeys) {
            foreach ($foreignKeys as $fk) {
                $table->dropForeign($fk->CONSTRAINT_NAME);
            }
        });
        
        // Rename the table
        Schema::rename('tasks', 'activities');
        
        // Recreate foreign key constraints with correct table name
        Schema::table('activities', function (Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('activities')) {
            // Table doesn't exist, nothing to rename back
            return;
        }
        
        // Drop foreign key constraints BEFORE renaming
        $connection = Schema::getConnection();
        $dbName = $connection->getDatabaseName();
        $tableName = 'activities';
        
        // Get foreign key constraint names from information_schema
        $foreignKeys = $connection->select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = ?
            AND COLUMN_NAME = 'project_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$dbName, $tableName]);
        
        Schema::table('activities', function (Blueprint $table) use ($foreignKeys) {
            foreach ($foreignKeys as $fk) {
                $table->dropForeign($fk->CONSTRAINT_NAME);
            }
        });
        
        // Rename the table back
        Schema::rename('activities', 'tasks');
        
        // Recreate foreign key constraints
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }
};
