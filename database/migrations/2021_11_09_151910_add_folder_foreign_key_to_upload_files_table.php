<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFolderForeignKeyToUploadFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('upload_files', function (Blueprint $table) {
            $table->foreignId('folder_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_delivery_note_id')->constrained()->cascadeOnDelete();
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
