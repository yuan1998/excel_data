<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeBiaduSpendTableAddSpendTypeAndCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('baidu_spends', function (Blueprint $table) {
            $table->string('spend_type')->nullable();
            $table->string('code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('baidu_spends', function (Blueprint $table) {
            $table->dropColumn('spend_type');
            $table->dropColumn('code');
        });
    }
}
