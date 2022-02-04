<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNullableForeignKeysInUploadFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('upload_files', function (Blueprint $table) {
            $table->string('path');
            $table->string('original_file_name');
            $table->string('size');
            $table->string('extension');
            $table->foreignId('order_id')->nullable()->constrained();
            $table->foreignId('purchase_id')->nullable()->constrained();
            $table->foreignId('delivery_note_id')->nullable()->constrained();
            $table->foreignId('purchase_order_id')->nullable()->constrained();
            $table->foreignId('sale_id')->nullable()->constrained();
            $table->foreignId('client_delivery_note_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('upload_files', function (Blueprint $table) {
            //
        });
    }
}
