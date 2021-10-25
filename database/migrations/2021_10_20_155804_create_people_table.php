<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('lastName')->nullable();
            $table->string('firstName')->nullable();
            $table->string('rccmNumber')->nullable();
            $table->string('ccNumber')->nullable();
            $table->string('socialReason')->nullable();
            $table->string('personType')->nullable();
            $table->string('personable_code')->nullable();
            $table->string('personable_type')->nullable();
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
        Schema::dropIfExists('people');
    }
}
