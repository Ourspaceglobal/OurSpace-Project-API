<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomApartmentKycsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_apartment_kycs', function (Blueprint $table) {
            $table->foreignUuid('apartment_id')->constrained();
            $table->foreignUuid('system_apartment_kyc_id')->constrained();
            $table->timestamps();

            $table->primary([
                'apartment_id',
                'system_apartment_kyc_id',
            ], 'cpk_apartment_id_system_apartment_kyc_id_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_apartment_kycs');
    }
}
