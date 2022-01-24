<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropForeignKeyFromRemovalOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('removal_orders', function (Blueprint $table) {
            $table->dropForeign(['stock_type_id']);
            $table->dropColumn('stock_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('removal_orders', function (Blueprint $table) {
            //
        });
    }
}
