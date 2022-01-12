<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropForeignKeyInTournsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tourns', function (Blueprint $table) {
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
        Schema::table('tourns', function (Blueprint $table) {
            //
        });
    }
}
