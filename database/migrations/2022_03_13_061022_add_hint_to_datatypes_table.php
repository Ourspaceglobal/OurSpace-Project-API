<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHintToDatatypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('datatypes', function (Blueprint $table) {
            $table->after('name', function ($table) {
                $table->string('hint')->nullable();
                $table->string('developer_hint')->nullable();
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
        Schema::table('datatypes', function (Blueprint $table) {
            $table->dropColumn([
                'hint',
                'developer_hint',
            ]);
        });
    }
}
