<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeWeiboSpendTableAddDiversionsField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('weibo_spends', function (Blueprint $table) {
            $table->string('diversions')->nullable();
            $table->string('diversions_fans')->nullable();
            $table->string('advertiser_account')->nullable();
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
            $table->dropColumn('diversions');
            $table->dropColumn('diversions_fans');
            $table->dropColumn('advertiser_account');
        });
    }
}
