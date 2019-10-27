<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBaiduClues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('BaiduClues', function (Blueprint $table) {
            $table->boolean('is_archive')->default(0);
            $table->string('phone');
            $table->unsignedBigInteger('baidu_id');

            $table->foreign('baidu_id')
                ->references('id')
                ->on('baidu_data')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('BaiduClues');
    }
}
