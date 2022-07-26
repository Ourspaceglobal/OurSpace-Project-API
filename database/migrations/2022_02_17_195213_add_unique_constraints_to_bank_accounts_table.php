<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUniqueConstraintsToBankAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->unique([
                'user_id',
                'account_name',
                'account_number',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique([
                'user_id',
                'account_name',
                'account_number',
            ]);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
}
