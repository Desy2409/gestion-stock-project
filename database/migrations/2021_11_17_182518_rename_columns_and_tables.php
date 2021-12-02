<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnsAndTables extends Migration
{


    public function listTableForeignKeys($table)
    {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();

        return array_map(function($key) {
            return $key->getName();
        }, $conn->listTableForeignKeys($table));
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $tables = ['purchase_orders', 'orders', 'purchase_coupons', 'product_purchase_coupons', 'purchase_coupon_registers'];

        //isiii
        foreach ($tables as $tablename){
            $value = get_object_vars($tablename)['Tables_in_'.$table];
            $foreignKeys = $this->listTableForeignKeys($value);
            Schema::table($value, function (Blueprint $table) use ($foreignKeys){
                foreach ($foreignKeys as $foreignKey){
                    $table->dropForeign($foreignKey);
                }
            });
        }

        Schema::rename('purchase_orders','old_purchase_orders');
        Schema::rename('orders','old_orders');

        Schema::rename('old_purchase_orders','orders');
        Schema::rename('old_orders','purchase_orders');

        Schema::rename('purchase_coupons', 'coupons');

        Schema::rename('product_purchase_coupons', 'product_coupons');

        Schema::rename('purchase_coupon_registers', 'coupon_registers');

        // Schema::rename('purchase_order_registers', 'order_registers');

        Schema::table('coupons', function(Blueprint $table){
            $table->dropForeign(['purchase_order_id']);//isiii
            $table->renameColumn('purchase_order_id','order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');//
        });

        Schema::table('delivery_notes', function(Blueprint $table){
            $table->dropForeign(['purchase_coupon_id']);//isiii
            $table->renameColumn('purchase_coupon_id','coupon_id');
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');//isiii
        });

        Schema::table('sales', function(Blueprint $table){
            $table->dropForeign(['order_id']);//isiii
            $table->renameColumn('order_id','purchase_order_id');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');//isiii
        });

        Schema::table('orders', function(Blueprint $table){
            $table->renameColumn('purchase_date','order_date');
        });

        Schema::table('purchase_orders', function(Blueprint $table){
            $table->renameColumn('order_date','purchase_date');
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
