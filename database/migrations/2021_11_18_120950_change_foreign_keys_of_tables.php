<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeForeignKeysOfTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_coupons', function(Blueprint $table){
            $table->renameColumn('purchase_coupon_id','coupon_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->date('date_of_processing')->useCurrent()->nullable();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->date('date_of_processing')->useCurrent()->nullable();
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
