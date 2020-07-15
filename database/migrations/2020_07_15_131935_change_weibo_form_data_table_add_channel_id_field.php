<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeWeiboFormDataTableAddChannelIdField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('weibo_form_data', function (Blueprint $table) {
            $table->unsignedBigInteger('channel_id')->index()->nullable();
        });
        Schema::table('weibo_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('channel_id')->index()->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('weibo_form_data', function (Blueprint $table) {
            $table->dropColumn('channel_id');
        });
        Schema::table('weibo_accounts', function (Blueprint $table) {
            $table->dropColumn('channel_id');
        });

    }
}
