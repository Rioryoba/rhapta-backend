<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinancialColumnsToExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->default(0)->after('reference');
            }

            if (!Schema::hasColumn('expenses', 'tax')) {
                $table->decimal('tax', 15, 2)->default(0)->after('subtotal');
            }

            if (!Schema::hasColumn('expenses', 'discount')) {
                $table->decimal('discount', 15, 2)->default(0)->after('tax');
            }

            if (!Schema::hasColumn('expenses', 'total')) {
                $table->decimal('total', 15, 2)->default(0)->after('discount');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'total')) {
                $table->dropColumn('total');
            }

            if (Schema::hasColumn('expenses', 'discount')) {
                $table->dropColumn('discount');
            }

            if (Schema::hasColumn('expenses', 'tax')) {
                $table->dropColumn('tax');
            }

            if (Schema::hasColumn('expenses', 'subtotal')) {
                $table->dropColumn('subtotal');
            }
        });
    }
}
