<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysAndRemoveUnitPriceFormTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tablesForUnityForeignKey=['product_purchase_orders','product_purchase_coupons','product_delivery_notes','product_orders','product_sales','product_client_delivery_notes'];
        $tablesForUnitPrice=['product_delivery_notes','product_client_delivery_notes'];

        foreach ($tablesForUnityForeignKey as $key => $table_name) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->foreignId('unity_id')->constrained()->cascadeOnDelete();
            });
        }

        foreach ($tablesForUnitPrice as $key => $table_name) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->dropColumn('unit_price');
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
        //
    }
}
