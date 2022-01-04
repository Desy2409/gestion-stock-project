<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInTournsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tourns', function (Blueprint $table) {
            $table->date('date_of_edition');
            $table->foreignId('client_delivery_note_id')->nullable();
            $table->foreignId('removal_order_id')->nullable();
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
