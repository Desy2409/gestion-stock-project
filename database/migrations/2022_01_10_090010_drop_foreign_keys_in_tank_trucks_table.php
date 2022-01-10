<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropForeignKeysInTankTrucksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tank_trucks', function (Blueprint $table) {
            $table->dropForeign(['tank_id']);
            $table->dropColumn('tank_id');
            $table->dropForeign(['truck_id']);
            $table->dropColumn('truck_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tank_trucks', function (Blueprint $table) {
            //
        });
    }
}
