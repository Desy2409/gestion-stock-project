<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileUploadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('mime');
            $table->string('original_filename');
            $table->string('filename');
            $table->string('link');
            $table->string('personalized_filename');
            $table->foreignId('file_type_id')->nullable()->constrained();
            $table->foreignId('folder_id')->nullable()->constrained();
            $table->foreignId('size')->nullable()->constrained();
            $table->foreignId('order_id')->nullable()->constrained();
            $table->foreignId('purchase_id')->nullable()->constrained();
            $table->foreignId('delivery_note_id')->nullable()->constrained();
            $table->foreignId('purchase_order_id')->nullable()->constrained();
            $table->foreignId('sale_id')->nullable()->constrained();
            $table->foreignId('client_delivery_note_id')->nullable()->constrained();
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
        Schema::dropIfExists('file_uploads');
    }
}
