<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeBaiduClueTableAddHasDialogIdAndHasUrl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('BaiduClues', function (Blueprint $table) {
            $table->boolean('has_dialog_id')->default(0);
            $table->boolean('has_url')->default(0);
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
            $table->dropColumn('has_dialog_id');
            $table->dropColumn('has_url');
        });
    }
}
