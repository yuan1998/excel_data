<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeWeiboFormDataTableAddDispatchDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('weibo_form_data', function (Blueprint $table) {
            $table->dateTime('dispatch_date')->nullable();
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
            $table->dropColumn('dispatch_date');
        });
    }
}
