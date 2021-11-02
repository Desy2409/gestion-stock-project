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
            $table->date('delivery_date_wished');
            $table->string('voucher_type');
            $table->string('customs_regime');
            $table->string('storage_unit');
            $table->string('carrier');
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_point_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
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
