<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeBaiduClueAddArrivingTypeField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('BaiduClues', function (Blueprint $table) {
            $table->integer('arriving_type')->default(0);
        });
        Schema::table('weibo_data', function (Blueprint $table) {
            $table->integer('arriving_type')->default(0);
        });
        Schema::table('feiyu_data', function (Blueprint $table) {
            $table->integer('arriving_type')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('BaiduClues', function (Blueprint $table) {
            $table->dropColumn('arriving_type');
        });
        Schema::table('weibo_data', function (Blueprint $table) {
            $table->dropColumn('arriving_type');
        });
        Schema::table('feiyu_data', function (Blueprint $table) {
            $table->dropColumn('arriving_type');
        });
    }
}
