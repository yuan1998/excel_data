<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeBillAccountDataTableAddArchiveIdField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bill_account_data', function (Blueprint $table) {
            $table->unsignedBigInteger('archive_id');
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
            $table->dropColumn('archive_id');
        });
    }
}
