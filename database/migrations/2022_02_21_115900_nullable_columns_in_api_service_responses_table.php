<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NullableColumnsInApiServiceResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_service_responses', function (Blueprint $table) {
            $table->string('response_type')->nullable()->change();
            $table->string('response_content')->nullable()->change();
            $table->string('response_state')->default('P')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_service_responses', function (Blueprint $table) {
            //
        });
    }
}
