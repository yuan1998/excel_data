<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSpendDataTableAddCodeFieldDataSnapField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spend_data', function (Blueprint $table) {
            $table->unsignedBigInteger('channel_id')->index()->nullable();
            $table->json('data_snap')->nullable();
            $table->string('uuid')->nullable();
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
        Schema::table('spend_data', function (Blueprint $table) {
            $table->dropColumn('channel_id');
            $table->dropColumn('data_snap');
            $table->dropColumn('uuid');
            $table->dropColumn('code');
        });
    }
}
