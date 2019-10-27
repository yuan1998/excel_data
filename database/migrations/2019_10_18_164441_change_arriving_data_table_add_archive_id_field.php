<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeArrivingDataTableAddArchiveIdField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('arriving_data', function (Blueprint $table) {
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
        Schema::table('arriving_data', function (Blueprint $table) {
            $table->dropColumn('archive_id');
        });
    }
}
