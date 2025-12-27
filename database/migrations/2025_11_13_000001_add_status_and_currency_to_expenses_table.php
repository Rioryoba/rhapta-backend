<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusAndCurrencyToExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'status')) {
                $table->string('status', 50)->default('pending')->after('total');
            }

            if (!Schema::hasColumn('expenses', 'currency')) {
                $table->string('currency', 10)->nullable()->after('status');
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
            if (Schema::hasColumn('expenses', 'currency')) {
                $table->dropColumn('currency');
            }

            if (Schema::hasColumn('expenses', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
}
