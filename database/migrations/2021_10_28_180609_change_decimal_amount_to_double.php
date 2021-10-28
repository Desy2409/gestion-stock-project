<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDecimalAmountToDouble extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tablesForTotalAmount = ['purchase_orders', 'purchase_coupons', 'delivery_notes', 'orders'];
        $tablesForUnitPrice = ['product_purchase_orders', 'product_transfer_demand_lines', 'product_transfer_lines', 'product_purchase_coupons', 'product_delivery_notes', 'product_orders'];

        foreach ($tablesForTotalAmount as $key => $table_name) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->dropColumn('total_amount');
            });
        }

        foreach ($tablesForTotalAmount as $key => $table_name) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->double('total_amount');
            });
        }

        foreach ($tablesForUnitPrice as $key => $table_name) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->dropColumn('unit_price');
            });
        }

        foreach ($tablesForUnitPrice as $key => $table_name) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->double('unit_price');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('double', function (Blueprint $table) {
            //
        });
    }
}
