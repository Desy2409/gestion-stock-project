<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropForeignKeysInUploadFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('upload_files', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->dropColumn('purchase_order_id');
            $table->dropForeign(['purchase_coupon_id']);
            $table->dropColumn('purchase_coupon_id');
            $table->dropForeign(['delivery_note_id']);
            $table->dropColumn('delivery_note_id');
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
            $table->dropForeign(['sale_id']);
            $table->dropColumn('sale_id');
            $table->dropForeign(['client_delivery_note_id']);
            $table->dropColumn('client_delivery_note_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('upload_files', function (Blueprint $table) {
            //
        });
    }
}
