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
        Schema::table('accounts', function (Blueprint $table) {
            // Make account_number nullable since we're using code now
            $table->integer('account_number')->nullable()->change();
            // Make account_type nullable since we're using category now
            $table->enum('account_type', ['asset', 'liability', 'equity', 'revenue', 'expense'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Revert to not nullable (but this might fail if there are null values)
            $table->integer('account_number')->nullable(false)->change();
            $table->enum('account_type', ['asset', 'liability', 'equity', 'revenue', 'expense'])->nullable(false)->change();
        });
    }
};
