<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable();
            $table->string('matricule')->nullable();
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->longText('roles')->nullable();
            $table->boolean('is_retired')->default(false)->nullable();
            $table->boolean('is_dead')->default(false)->nullable();
            $table->foreignId('employee_function_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_type_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
