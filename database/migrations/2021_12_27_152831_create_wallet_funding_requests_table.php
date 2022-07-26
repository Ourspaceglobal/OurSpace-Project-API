<?php

use App\Enums\WalletFundingStatuses;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletFundingRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_funding_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained();
            $table->decimal('amount', 36, 2);
            $table->string('status')->default(WalletFundingStatuses::PENDING);
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
        Schema::dropIfExists('wallet_funding_requests');
    }
}
