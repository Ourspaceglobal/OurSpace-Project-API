<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApartmentGalleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apartment_galleries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('apartment_id')->constrained();
            $table->string('title');
            $table->timestamps();

            $table->unique([
                'apartment_id',
                'title',
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
        Schema::dropIfExists('apartment_galleries');
    }
}
