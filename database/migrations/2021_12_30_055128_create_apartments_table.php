<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained();
            $table->foreignUuid('user_id')->constrained();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 36, 2);
            $table->foreignUuid('apartment_duration_id')->constrained();
            $table->boolean('is_active')->default(false);
            $table->timestamp('activated_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_at')->nullable();
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
        Schema::dropIfExists('apartments');
    }
}
