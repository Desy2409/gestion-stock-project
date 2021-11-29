<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUnitPriceAndAddUnityIdInProductTransferDemandsAndTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = ['product_transfer_demand_lines', 'product_transfer_lines'];
        foreach ($tables as $key => $table_name) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->dropColumn('unit_price');
                $table->foreignId('unity_id')->constrained()->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transfer_demands_and_transfers', function (Blueprint $table) {
            //
        });
    }
}
