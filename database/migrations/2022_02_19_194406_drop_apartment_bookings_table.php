<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropApartmentBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('apartment_bookings');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('apartment_bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('apartment_id')->constrained();
            $table->foreignUuid('user_id')->constrained();
            $table->text('note')->nullable();
            $table->timestamp('intended_date');
            $table->timestamp('approved_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
