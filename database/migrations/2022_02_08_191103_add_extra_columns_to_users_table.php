<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->after('is_2fa_active', function ($table) {
                $table->string('country')->nullable();
                $table->string('state')->nullable();
                $table->string('gender')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->string('home_address')->nullable();
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'country',
                'state',
                'gender',
                'date_of_birth',
                'home_address',
            ]);
        });
    }
}
