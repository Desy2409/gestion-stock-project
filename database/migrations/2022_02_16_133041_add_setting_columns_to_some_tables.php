<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSettingColumnsToSomeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            'orders', 'purchases', 'delivery_notes', 'purchase_orders', 'sales', 'client_delivery_notes',
            'transfer_demands', 'removal_orders', 'tourns'
        ];
        foreach ($tables as $key => $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->integer('validation_number')->default(0);
                $table->integer('validation_level')->default(0);
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
        Schema::table('some_tables', function (Blueprint $table) {
            //
        });
    }
}
