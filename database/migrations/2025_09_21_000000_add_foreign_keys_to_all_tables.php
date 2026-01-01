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
            if (!Schema::hasColumn('employees', 'department_id')) {
                $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            }
            if (!Schema::hasColumn('employees', 'position_id')) {
                $table->foreignId('position_id')->constrained('positions')->onDelete('cascade');
            }
        });

        // Departments foreign key
        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'manager_id')) {
                $table->foreignId('manager_id')->nullable()->constrained('employees')->onDelete('set null');
            }
        });

        // Users foreign keys
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'employee_id')) {
                $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('set null');
            }
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null');
            }
        });

        // Projects foreign keys
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'manager_id')) {
                $table->unsignedBigInteger('manager_id')->nullable()->change();
            }
            if (Schema::hasColumn('projects', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->change();
            }
        });

        // Tasks foreign keys
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'project_id')) {
                $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            }
            if (Schema::hasColumn('tasks', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable()->change();
            }
        });

        // Notifications foreign key
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'user_id')) {
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // Documents foreign key
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'uploaded_by')) {
                $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            }
        });

        // Audit logs foreign key
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'user_id')) {
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            }
        });

        // Maintenance records foreign key


        // Contracts foreign keys
        Schema::table('contracts', function (Blueprint $table) {
            if (Schema::hasColumn('contracts', 'type_id')) {
                $table->unsignedBigInteger('type_id')->nullable()->change();
            }
            if (!Schema::hasColumn('contracts', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            }
            if (!Schema::hasColumn('contracts', 'employee_id')) {
                $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('cascade');
            }
            if (!Schema::hasColumn('contracts', 'position_id')) {
                $table->foreignId('position_id')->nullable()->constrained('positions')->onDelete('set null');
            }
        });

        // Sales orders foreign key
        Schema::table('sales_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_orders', 'customer_id')) {
                $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            }
        });

        // Payments foreign key
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'invoice_id')) {
                $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            }
        });

        // Invoices foreign key
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'customer_id')) {
                $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            }
        });

        // Expenses foreign key
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'account_id')) {
                $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            }
        });

        // Payrolls foreign key
        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'employee_id')) {
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            }
        });

        // Leaves foreign key
        Schema::table('leaves', function (Blueprint $table) {
            if (!Schema::hasColumn('leaves', 'employee_id')) {
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            }
        });

        // Attendences foreign key
        Schema::table('attendences', function (Blueprint $table) {
            if (!Schema::hasColumn('attendences', 'employee_id')) {
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            }
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
    // public function down(): void
    // // {
    // //     Drop all foreign keys added in up()
    // //     (You may want to add code here to drop the foreign keys if needed)
    // }
};
