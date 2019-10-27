<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('weibo_id')->nullable()->index();
            $table->string('baidu_id')->nullable()->index();
            $table->string('feiyu_id')->nullable()->index();
            $table->string('archive_type')->nullable();
            $table->integer('form_type')->default(0)->index();
            $table->string('date')->index();
            $table->string('data_type')->nullable();

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
        Schema::dropIfExists('form_data');
    }
}
