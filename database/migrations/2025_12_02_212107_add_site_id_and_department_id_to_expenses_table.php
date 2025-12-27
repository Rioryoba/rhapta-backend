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
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->after('account_id')->constrained('sites')->onDelete('set null');
            $table->foreignId('department_id')->nullable()->after('site_id')->constrained('departments')->onDelete('set null');
            $table->string('category')->nullable()->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn(['site_id', 'department_id', 'category']);
        });
    }
};
