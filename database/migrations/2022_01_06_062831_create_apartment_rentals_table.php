<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApartmentRentalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apartment_rentals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('apartment_id')->constrained();
            $table->foreignUuid('user_id')->constrained();
            $table->foreignUuid('payment_transaction_id')->constrained();
            $table->timestamp('started_at');
            $table->timestamp('expired_at');
            $table->boolean('is_autorenewal_active')->default(true);
            $table->timestamp('terminated_at')->nullable();
            $table->text('termination_reason')->nullable();
            $table->timestamp('last_reminder_sent_at')->nullable();
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
        Schema::dropIfExists('apartment_rentals');
    }
}
