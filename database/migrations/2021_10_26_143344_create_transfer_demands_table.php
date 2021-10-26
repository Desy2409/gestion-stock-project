<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Schema;

class CreateTransferDemandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_demands', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('request_reason');
            $table->date('date_of_demand')->useCurrent();
            $table->date('delivery_deadline');
            $table->date('date_of_processing');
            $table->string('state')->default('P');
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
        Schema::dropIfExists('transfer_demands');
    }
}
