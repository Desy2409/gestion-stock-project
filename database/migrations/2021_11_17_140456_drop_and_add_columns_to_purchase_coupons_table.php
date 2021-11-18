<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropAndAddColumnsToPurchaseCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_coupons', function (Blueprint $table) {
            $table->renameColumn('total_amount', 'amount_gross');
            $table->double('ht_amount')->default(0)->nullable();
            $table->double('discount')->default(0)->nullable();
            $table->double('amount_token')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_coupons', function (Blueprint $table) {
            //
        });
    }
}
