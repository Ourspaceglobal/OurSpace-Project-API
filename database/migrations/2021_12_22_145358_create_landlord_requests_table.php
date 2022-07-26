<?php

use App\Enums\LandlordRequestStatuses;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandlordRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('landlord_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained();
            $table->text('note')->nullable();
            $table->string('status')->default(LandlordRequestStatuses::PENDING);
            $table->boolean('is_declined')->default(false);
            $table->text('declination_reason')->nullable();
            $table->timestamp('declined_at')->nullable();
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
        Schema::dropIfExists('landlord_requests');
    }
}
