<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeBillAccountAddMediumTYpeIdField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bill_account_data', function (Blueprint $table) {
            $table->unsignedBigInteger('medium_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bill_account_data', function (Blueprint $table) {
            $table->dropColumn('medium_id');
        });
    }
}
