<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropDeliveryPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_delivery_point', function (Blueprint $table) {
            $table->dropForeign(['delivery_point_id']);
            $table->dropColumn(['delivery_point_id']);
        });
        Schema::dropIfExists('client_delivery_point');
        Schema::dropIfExists('delivery_points');
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
