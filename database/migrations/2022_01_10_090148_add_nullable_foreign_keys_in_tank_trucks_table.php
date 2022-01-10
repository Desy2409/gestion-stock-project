<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNullableForeignKeysInTankTrucksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tank_trucks', function (Blueprint $table) {
            $table->foreignId('tank_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('truck_id')->nullable()->constrained()->cascadeOnDelete();
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
