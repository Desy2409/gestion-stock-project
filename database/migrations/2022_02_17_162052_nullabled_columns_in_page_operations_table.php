<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NullabledColumnsInPageOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('page_operations', function (Blueprint $table) {
            $table->string('code')->nullable()->change();
            $table->string('title')->nullable()->change();
            $table->dropColumn('role');
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
