<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFkToWithdrawalRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->foreign('bank_account_id')
                ->references('id')
                ->on('bank_accounts')
                ->cascadeOnDelete();
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
            $table->dropForeign(['bank_account_id']);
        });
    }
}
