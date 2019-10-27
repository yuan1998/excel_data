<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpendDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spend_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('baidu_id')->nullable();
            $table->string('feiyu_id')->nullable();
            $table->string('weibo_id')->nullable();
            $table->string('date')->index();
            $table->float('spend')->default(0);
            $table->integer('spend_type')->default(0);
            $table->unsignedInteger('show')->default(0);
            $table->unsignedInteger('click')->default(0);
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
        Schema::dropIfExists('spend_data');
    }
}
