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
        // Employees foreign keys
        Schema::table('employees', function (Blueprint $table) {
            // $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            // $table->foreignId('position_id')->constrained('positions')->onDelete('cascade');
        });

        // Departments foreign key
        Schema::table('departments', function (Blueprint $table) {
            // $table->foreignId('manager_id')->nullable()->constrained('employees')->onDelete('set null');
        });

        // Users foreign keys
        Schema::table('users', function (Blueprint $table) {
            // $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('set null');
            // $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null');
        });

        // Projects foreign keys
        Schema::table('projects', function (Blueprint $table) {
            // $table->unsignedBigInteger('manager_id')->nullable()->change();
            // $table->unsignedBigInteger('department_id')->nullable()->change();
            // $table->foreign('manager_id')->references('id')->on('employees')->onDelete('set null');
            // $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });

        // Tasks foreign keys
        Schema::table('tasks', function (Blueprint $table) {
            // $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            // $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            // $table->unsignedBigInteger('assigned_to')->nullable()->change();
            // $table->foreign('assigned_to')->references('id')->on('employees')->onDelete('set null');
        });

        // Notifications foreign key
        Schema::table('notifications', function (Blueprint $table) {
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        });

        // Documents foreign key
        Schema::table('documents', function (Blueprint $table) {
            // $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
        });

        // Audit logs foreign key
        Schema::table('audit_logs', function (Blueprint $table) {
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        });

        // Contracts foreign keys
        Schema::table('contracts', function (Blueprint $table) {
            // $table->foreignId('type_id')->constrained('contract_types')->onDelete('cascade');
            // $table->foreign('type_id')->references('id')->on('contract_types')->onDelete('cascade');
            // $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            // $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('cascade');
            // $table->foreignId('position_id')->nullable()->constrained('positions')->onDelete('set null');
        });

        // Sales orders foreign key
        Schema::table('sales_orders', function (Blueprint $table) {
            // $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
        });

        // Payments foreign key
        Schema::table('payments', function (Blueprint $table) {
            // $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
        });

        // Invoices foreign key
        Schema::table('invoices', function (Blueprint $table) {
            // $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
        });

        // Expenses foreign key
        Schema::table('expenses', function (Blueprint $table) {
            // $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
        });

        // Payrolls foreign key
        Schema::table('payrolls', function (Blueprint $table) {
            // $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
        });

        // Leaves foreign key
        Schema::table('leaves', function (Blueprint $table) {
            // $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
        });

        // Attendences foreign key
        Schema::table('attendences', function (Blueprint $table) {
            // $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
        });

        // Positions foreign key
        Schema::table('positions', function (Blueprint $table) {
            if (!Schema::hasColumn('positions', 'department_id')) {
                $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all foreign keys added in up()
        // TODO: Implement the down method to drop all foreign keys
    }
};