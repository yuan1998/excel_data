<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeArrivingAddMediumTYpeIdField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('arriving_data', function (Blueprint $table) {
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
        Schema::table('arriving_data', function (Blueprint $table) {
            $table->dropColumn('medium_id');
        });
    }
}
