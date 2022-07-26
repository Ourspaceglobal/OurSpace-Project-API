<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCheckingDatesToApartmentRentalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('apartment_rentals', function (Blueprint $table) {
            $table->after('is_autorenewal_active', function ($table) {
                $table->timestamp('check_in_date')->nullable();
                $table->timestamp('check_out_date')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('apartment_rentals', function (Blueprint $table) {
            //
        });
    }
}
