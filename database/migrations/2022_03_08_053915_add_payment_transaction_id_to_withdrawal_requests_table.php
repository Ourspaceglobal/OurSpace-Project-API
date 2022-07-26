<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentTransactionIdToWithdrawalRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->foreignUuid('payment_transaction_id')
                ->nullable()
                ->after('bank_account_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropForeign(['payment_transaction_id']);

            $table->dropColumn('payment_transaction_id');
        });
    }
}
