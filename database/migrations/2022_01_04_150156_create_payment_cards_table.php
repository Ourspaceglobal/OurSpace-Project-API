<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('user');
            $table->string('authorization_code');
            $table->string('card_type')->nullable();
            $table->integer('first_6digits');
            $table->integer('last_4digits');
            $table->integer('expiry_month');
            $table->year('expiry_year');
            $table->string('bank')->nullable();
            $table->string('country_code')->nullable();
            $table->string('account_name')->nullable();
            $table->string('payment_gateway');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_cards');
    }
}
