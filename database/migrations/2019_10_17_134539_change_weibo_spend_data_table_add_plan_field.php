<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeWeiboSpendDataTableAddPlanField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('weibo_spends', function (Blueprint $table) {
            $table->string('advertiser_plan')->nullable();
            $table->dropColumn('advertiser_account');

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
            $table->dropColumn('advertiser_plan');
            $table->string('advertiser_account')->nullable();
        });
    }
}
