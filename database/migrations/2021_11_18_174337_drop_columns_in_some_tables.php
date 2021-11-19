<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumnsInSomeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_orders', function(Blueprint $table){
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });

        Schema::table('product_purchase_orders', function(Blueprint $table){
            $table->dropForeign(['purchase_order_id']);
            $table->dropColumn('purchase_order_id');
        });

        Schema::table('product_coupons', function(Blueprint $table){
            $table->renameColumn('coupon_id','purchase_coupon_id');
        });

        Schema::rename('coupons', 'purchase_coupons');
        Schema::rename('product_coupons', 'product_purchase_coupons');
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
