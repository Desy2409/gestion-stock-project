<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductTransferDemandLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_transfer_demand_lines', function (Blueprint $table) {
            $table->id();
            $table->decimal('quantity', 5, 2)->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transfer_demand_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('product_transfer_demand_lines');
    }
}
