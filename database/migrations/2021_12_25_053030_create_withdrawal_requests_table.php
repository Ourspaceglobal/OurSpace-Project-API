<?php

use App\Enums\WithdrawalRequestStatuses;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawalRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained();
            $table->foreignUuid('bank_user_id')->constrained('bank_user');
            $table->decimal('amount', 36, 2);
            $table->string('reason')->nullable();
            $table->string('status')->default(WithdrawalRequestStatuses::PENDING);
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
        Schema::dropIfExists('withdrawal_requests');
    }
}
