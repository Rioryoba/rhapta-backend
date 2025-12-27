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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('type_id')->constrained('contract_types')->onDelete('cascade'); // Foreign key temporarily commented out
            $table->string('contract_number')->unique();
            // $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade'); // Foreign key temporarily commented out
            // $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('cascade'); // Foreign key temporarily commented out
            // $table->foreignId('position_id')->nullable()->constrained('positions')->onDelete('set null'); // Foreign key temporarily commented out
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->enum('status', ['active', 'expired', 'terminated'])->default('active');
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
