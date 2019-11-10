<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecallLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recall_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tags')->nullable();
            $table->string('comment')->nullable();
            $table->unsignedBigInteger('weibo_user_id')->nullable();
            $table->unsignedBigInteger('weibo_form_id');
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
        Schema::dropIfExists('recall_logs');
    }
}
