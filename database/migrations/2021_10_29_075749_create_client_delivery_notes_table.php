<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientDeliveryNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->date('delivery_note_date')->useCurrent();
            $table->date('delivery_date');
            $table->double('total_amount')->default(0);
            $table->string('observation')->nullable();
            $table->foreignId('sale_id')->nullable()->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('client_delivery_notes');
    }
}
