<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeWeiboFormDataTableAddTypeField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('weibo_form_data', function (Blueprint $table) {
            $table->string('type')->default('kq');
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
            $table->dropColumn('type');

        });
    }
}
