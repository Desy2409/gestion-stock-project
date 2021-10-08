<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
            $table->string('order_number');
            $table->date('purchase_date');
            $table->date('delevery_date');
            $table->decimal('total_amount', 10, 2);
            $table->string('observation')->nullable();
            $table->timestamps();
            // $table->unsignedBigInteger('client_id')->nullable();
            // $table->unsignedBigInteger('provider_id')->nullable();
            // $table->unsignedBigInteger('product_id');
            // $table->foreign('client_id')->references('id')->on('clients');
            // $table->foreign('provider_id')->references('id')->on('providers');
            // $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_orders');
    }
}
