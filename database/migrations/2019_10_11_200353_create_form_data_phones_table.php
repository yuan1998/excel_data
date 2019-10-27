<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormDataPhonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_data_phones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('form_data_id')->index();
            $table->string('phone')->index();
            $table->integer('is_archive')->default(0)->index();
            $table->integer('intention')->default(0)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('form_data_phones');
    }
}
