<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeWeiboAccountsTableAddStartField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('weibo_accounts', function (Blueprint $table) {
            $table->boolean('enable_cpl')->default(false);
            $table->boolean('enable_lingdong')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('weibo_accounts', function (Blueprint $table) {
            $table->dropColumn('enable_cpl');
            $table->dropColumn('enable_lingdong');
        });
    }
}
