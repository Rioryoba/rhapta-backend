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
        // If the expenses table doesn't exist, skip to avoid crashing when this migration
        // is run in isolation. Prefer running the create-expenses migration first.
        if (!Schema::hasTable('expenses')) {
            return;
        }

        Schema::table('expenses', function (Blueprint $table) {
            // Add nullable requested_by and received_by columns pointing to employees
            if (!Schema::hasColumn('expenses', 'requested_by')) {
                $table->foreignId('requested_by')->nullable()->constrained('employees')->onDelete('set null');
            }
            if (!Schema::hasColumn('expenses', 'received_by')) {
                $table->foreignId('received_by')->nullable()->constrained('employees')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('expenses')) {
            return;
        }

        Schema::table('expenses', function (Blueprint $table) {
            // Drop foreign keys first (naming follows Laravel's convention)
            if (Schema::hasColumn('expenses', 'requested_by')) {
                $table->dropForeign(['requested_by']);
                $table->dropColumn('requested_by');
            }
            if (Schema::hasColumn('expenses', 'received_by')) {
                $table->dropForeign(['received_by']);
                $table->dropColumn('received_by');
            }
        });
    }
};