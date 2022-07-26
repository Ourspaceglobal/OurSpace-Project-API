<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DisableAutorenewalDefaultInApartmentRentalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('apartment_rentals', function (Blueprint $table) {
            $table->boolean('is_autorenewal_active')->default(false)->change();
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
            $table->boolean('is_autorenewal_active')->default(true)->change();
        });
    }
}
