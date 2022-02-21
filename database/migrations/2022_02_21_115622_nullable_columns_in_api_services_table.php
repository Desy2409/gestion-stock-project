<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NullableColumnsInApiServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_services', function (Blueprint $table) {
            $table->string('authorization_type')->nullable()->change();
            $table->string('authorization_user')->nullable()->change();
            $table->string('authorization_password')->nullable()->change();
            $table->string('authorization_token')->nullable()->change();
            $table->string('authorization_prefix')->nullable()->change();
            $table->string('authorization_key')->nullable()->change();
            $table->string('authorization_value')->nullable()->change();
            $table->string('body_type')->nullable()->change();
            $table->string('body_content')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_services', function (Blueprint $table) {
            //
        });
    }
}
