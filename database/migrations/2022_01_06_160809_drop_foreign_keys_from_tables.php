<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropForeignKeysFromTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trucks', function (Blueprint $table) {
            $table->dropForeign(['provider_id']);
            $table->dropColumn('provider_id');
            // $table->foreignId('provider_id')->nullable()->constrained()->cascadeOnDelete();
        });
        Schema::table('tanks', function (Blueprint $table) {
            $table->dropForeign(['provider_id']);
            $table->dropColumn('provider_id');
            // $table->foreignId('provider_id')->nullable()->constrained()->cascadeOnDelete();
        });

        Schema::table('compartments', function (Blueprint $table) {
            $table->dropForeign(['tank_id']);
            $table->dropColumn('tank_id');
            // $table->foreignId('provider_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trucks', function (Blueprint $table) {
            //
        });
    }
}
