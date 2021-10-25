<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->decimal('quantity', 5, 2)->default(0);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->string('product_code');
            $table->foreign('product_code')->references('code')->on('products');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_purchase_orders');
    }
}
