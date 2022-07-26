<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bank_id')->constrained();
            $table->foreignUuid('user_id')->constrained();
            $table->string('account_number');
            $table->string('sort_code')->nullable();
            $table->timestamps();

            $table->unique([
                'bank_id',
                'user_id',
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
        Schema::dropIfExists('bank_user');
    }
}
