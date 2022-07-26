<?php

use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('user');
            $table->nullableUuidMorphs('model');
            $table->string('payment_purpose');
            $table->string('payment_gateway');
            $table->decimal('amount', 36, 2);
            $table->string('currency');
            $table->string('reference')->unique();
            $table->string('payment_method')->nullable();
            $table->string('status')->default(PaymentStatus::PENDING);
            $table->string('narration')->nullable();
            $table->json('metadata');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_transactions');
    }
}
