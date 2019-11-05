<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeiboDispatchSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weibo_dispatch_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->string('rule_name');
            $table->string('keyword');
            $table->boolean('dispatch_open')->default(0);
            $table->boolean('all_day')->default(1);
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->integer('order')->default(0);
            $table->json('dispatch_users')->nullable();
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
        Schema::dropIfExists('weibo_dispatch_settings');
    }
}
