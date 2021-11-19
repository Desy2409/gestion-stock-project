<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReplaceCouponIdInDeliveryNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->renameColumn('coupon_id', 'purchase_coupon_id');
        });

        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropForeign(['purchase_coupon_id']);
            $table->dropColumn('purchase_coupon_id');
        });
        
        Schema::table('delivery_notes', function (Blueprint $table) {
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
        Schema::table('delivery_notes', function (Blueprint $table) {
            //
        });
    }
}
