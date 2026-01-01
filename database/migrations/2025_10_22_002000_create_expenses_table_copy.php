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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            // foreign key to accounts table
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            // amount and metadata
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('USD');
            $table->text('description')->nullable();
            $table->date('expense_date')->nullable();
            $table->string('reference')->nullable();
            // optional who recorded the expense
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            // who requested the expense (employee) and who will receive the money (employee)
            $table->foreignId('requested_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignId('received_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};