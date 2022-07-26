<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentTransactionIdToWalletFundingRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wallet_funding_requests', function (Blueprint $table) {
            $table->foreignUuid('payment_transaction_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wallet_funding_requests', function (Blueprint $table) {
            $table->dropForeign(['payment_transaction_id']);

            $table->dropColumn('payment_transaction_id');
        });
    }
}
