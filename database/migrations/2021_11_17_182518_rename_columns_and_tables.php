<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnsAndTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::rename('purchase_orders','old_purchase_orders');
        Schema::rename('orders','old_orders');

        Schema::rename('old_purchase_orders','orders');
        Schema::rename('old_orders','purchase_orders');

        Schema::rename('purchase_coupons', 'coupons');

        Schema::rename('product_purchase_coupons', 'product_coupons');
        Schema::rename('purchase_coupon_id', 'coupon_id');

        Schema::rename('product_purchase_orders', 'product_orders');
        // Schema::rename('purchase_order_id', 'order_id');

        Schema::rename('purchase_coupon_registers', 'coupon_registers');
        // Schema::rename('purchase_order_registers', 'order_registers');

        Schema::table('coupons', function(Blueprint $table){
            $table->renameColumn('purchase_order_id','order_id');
        });

        Schema::table('delivery_notes', function(Blueprint $table){
            $table->renameColumn('purchase_coupon_id','coupon_id');
        });

        Schema::table('sales', function(Blueprint $table){
            $table->renameColumn('order_id','purchase_order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
