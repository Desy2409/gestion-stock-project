<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimeOutToEmailChannelParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_channel_params', function (Blueprint $table) {
            $table->integer('reception_protocol')->nullable();
            $table->integer('time_out')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_channel_params', function (Blueprint $table) {
            //
        });
    }
}
