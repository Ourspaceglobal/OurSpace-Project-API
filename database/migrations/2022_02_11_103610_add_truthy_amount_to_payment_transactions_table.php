<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTruthyAmountToPaymentTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->decimal('truthy_amount', 36, 2)->nullable()->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn('truthy_amount');
        });
    }
}
