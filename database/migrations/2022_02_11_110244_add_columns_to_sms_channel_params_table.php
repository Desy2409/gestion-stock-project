<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToSmsChannelParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_channel_params', function (Blueprint $table) {
            $table->string('url');
            $table->string('user');
            $table->string('password');
            $table->string('sender');
            $table->json('type');
            $table->json('sms_header_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sms_channel_params', function (Blueprint $table) {
            //
        });
    }
}
