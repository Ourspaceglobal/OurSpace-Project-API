<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsMandatoryToSystemApartmentKycsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('system_apartment_kycs', function (Blueprint $table) {
            $table->boolean('is_required')->default(false)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('system_apartment_kycs', function (Blueprint $table) {
            $table->dropColumn('is_required');
        });
    }
}
