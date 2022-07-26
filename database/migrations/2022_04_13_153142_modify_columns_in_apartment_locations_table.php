<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyColumnsInApartmentLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('apartment_locations', function (Blueprint $table) {
            $table->uuid('state_id')->nullable()->change();
            $table->uuid('city_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('apartment_locations', function (Blueprint $table) {
            $table->uuid('state_id')->nullable(false)->change();
            $table->uuid('city_id')->nullable(false)->change();
        });
    }
}
