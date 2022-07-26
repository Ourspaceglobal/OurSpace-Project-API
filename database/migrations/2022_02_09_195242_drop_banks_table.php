<?php

use App\Enums\WithdrawalRequestStatuses;
use App\Models\WithdrawalRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropForeign(['bank_user_id']);
        });

        WithdrawalRequest::truncate();

        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->renameColumn('bank_user_id', 'bank_account_id');
        });

        Schema::drop('bank_user');
        Schema::drop('banks');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
