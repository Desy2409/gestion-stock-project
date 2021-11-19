<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemovedForeignKeyToSomeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_purchase_coupons', function(Blueprint $table){
            $table->dropForeign(['purchase_coupon_id']);
            $table->dropColumn('purchase_coupon_id');
        });

        Schema::table('product_orders', function(Blueprint $table){
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
        });

        Schema::table('product_purchase_orders', function(Blueprint $table){
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
        });

        Schema::rename('purchase_coupons', 'purchases');
        Schema::rename('product_purchase_coupons', 'product_purchases');
        Schema::rename('coupon_registers', 'purchase_registers');

        Schema::table('product_purchases', function(Blueprint $table){
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('some_tables', function (Blueprint $table) {
            //
        });
    }
}
