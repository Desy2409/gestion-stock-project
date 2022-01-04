<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToPageOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('page_operations', function (Blueprint $table) {
            $table->string('role')->nullable();
            $table->foreignId('operation_id')->nullable();
            $table->foreignId('page_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('page_operations', function (Blueprint $table) {
            //
        });
    }
}
