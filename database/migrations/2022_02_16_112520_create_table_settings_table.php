<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_settings', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->string('code_min_length')->nullable();
            $table->integer('validation_number')->nullable()->default(0);
            $table->integer('validation_reminder')->nullable()->default(0);
            $table->integer('principal_currency')->nullable();
            // $table->integer('unity_measure')->nullable();
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
        Schema::dropIfExists('table_settings');
    }
}
