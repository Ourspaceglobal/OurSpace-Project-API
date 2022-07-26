<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocalGovernmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('local_governments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('city_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();

            $table->unique([
                'city_id',
                'name',
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
        Schema::dropIfExists('local_governments');
    }
}
