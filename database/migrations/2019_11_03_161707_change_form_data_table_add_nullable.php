<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFormDataTableAddNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_data', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_data', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable(false)->change();

        });
    }
}
