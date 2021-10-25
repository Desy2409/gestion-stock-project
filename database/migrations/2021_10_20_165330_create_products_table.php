<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('reference');
            $table->string('wording');
            $table->string('description')->nullable();
            $table->string('typeStock');
            $table->decimal('price', 10, 2);
            $table->unsignedBigInteger('unity_id');
            $table->foreign('unity_id')->references('id')->on('unities');
            $table->unsignedBigInteger('sub_category_id');
            $table->foreign('sub_category_id')->references('id')->on('sub_categories');
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
        Schema::dropIfExists('products');
    }
}
