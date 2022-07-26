<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApartmentLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apartment_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('apartment_id')->constrained();
            $table->foreignUuid('state_id')->constrained();
            $table->foreignUuid('city_id')->constrained();
            $table->foreignUuid('local_government_id')->nullable()->constrained();
            $table->string('house_number');
            $table->string('street');
            $table->string('landmark');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
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
        Schema::dropIfExists('apartment_locations');
    }
}
