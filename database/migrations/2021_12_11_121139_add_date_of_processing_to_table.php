<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateOfProcessingToTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = ['purchases', 'delivery_notes', 'sales', 'client_delivery_notes'];

        foreach ($tables as $key => $table_name) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->date('date_of_processing')->useCurrent()->nullable();
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
        
    }
}
