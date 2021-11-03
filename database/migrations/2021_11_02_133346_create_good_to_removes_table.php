<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodToRemovesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('good_to_removes', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('reference');
            $table->date('voucher_date');
            $table->date('delivery_date_wished')->nullable();
            $table->string('place_of_delivery')->nullable();
            $table->string('voucher_type');
            $table->string('customs_regime');
            $table->unsignedBigInteger('storage_unit_id');
            $table->foreign('storage_unit_id')->references('id')->on('providers');
            $table->unsignedBigInteger('carrier_id');
            $table->foreign('carrier_id')->references('id')->on('providers');
            $table->foreignId('client_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('transmitter_id')->nullable();
            $table->foreign('transmitter_id')->references('id')->on('sale_points');
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->foreign('receiver_id')->references('id')->on('sale_points');
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
        Schema::dropIfExists('good_to_removes');
    }
}
