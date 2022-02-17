<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_services', function (Blueprint $table) {
            $table->id();
            $table->string('authorization_type');
            $table->string('authorization_user');
            $table->string('authorization_password');
            $table->string('authorization_token');
            $table->string('authorization_prefix');
            $table->string('authorization_key');
            $table->string('authorization_value');
            $table->string('body_type');
            $table->string('body_content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_services');
    }
}
