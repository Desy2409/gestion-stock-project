<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropCompartmentIdInTanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tanks', function (Blueprint $table) {
            $table->dropForeign(['compartment_id']);
            $table->dropColumn('compartment_id');
            $table->double('capacity')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tanks', function (Blueprint $table) {
            //
        });
    }
}
