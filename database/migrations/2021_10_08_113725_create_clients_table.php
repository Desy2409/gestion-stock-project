<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('rccm_number')->nullable();
            $table->string('cc_number')->nullable();
            $table->string('social_reason')->nullable();
            $table->string('address');
            $table->string('email');
            $table->string('bp')->nullable();
            $table->string('phone');
            $table->timestamps();
            $table->unsignedBigInteger('juridic_personality_id');
            $table->foreign('juridic_personality_id')->references('id')->on('juridic_personalities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
