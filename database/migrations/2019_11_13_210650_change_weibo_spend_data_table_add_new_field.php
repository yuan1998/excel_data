<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeWeiboSpendDataTableAddNewField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('weibo_spends', function (Blueprint $table) {
            $table->string('comment_count')->nullable();
            $table->string('comment_count_fans')->nullable();
            $table->string('follow_count')->nullable();
            $table->string('follow_count_fans')->nullable();
            $table->string('form_count')->nullable();
            $table->string('form_count_fans')->nullable();
            $table->string('like_count')->nullable();
            $table->string('like_count_fans')->nullable();
            $table->string('share_count')->nullable();
            $table->string('share_count_fans')->nullable();
            $table->string('start_count')->nullable();
            $table->string('start_count_fans')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('weibo_spends', function (Blueprint $table) {
            $table->dropColumn('comment_count');
            $table->dropColumn('comment_count_fans');
            $table->dropColumn('follow_count');
            $table->dropColumn('follow_count_fans');
            $table->dropColumn('form_count');
            $table->dropColumn('form_count_fans');
            $table->dropColumn('like_count');
            $table->dropColumn('like_count_fans');
            $table->dropColumn('share_count');
            $table->dropColumn('share_count_fans');
            $table->dropColumn('start_count');
            $table->dropColumn('start_count_fans');
        });
    }
}
