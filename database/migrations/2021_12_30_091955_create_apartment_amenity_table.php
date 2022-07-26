<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApartmentAmenityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apartment_amenity', function (Blueprint $table) {
            $table->foreignUuid('apartment_id')->constrained();
            $table->foreignUuid('amenity_id')->constrained();
            $table->integer('total_number');
            $table->timestamps();

            $table->primary([
                'apartment_id',
                'amenity_id',
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
        Schema::dropIfExists('apartment_amenity');
    }
}
