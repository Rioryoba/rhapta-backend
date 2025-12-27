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
            $table->string('code')->nullable()->after('account_number');
            $table->enum('category', ['Assets', 'Liabilities', 'Equity', 'Income', 'Expenses'])->nullable()->after('account_type');
            $table->enum('type', ['Debit', 'Credit'])->nullable()->after('category');
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->onDelete('cascade')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['code', 'category', 'type', 'parent_id']);
        });
    }
};
