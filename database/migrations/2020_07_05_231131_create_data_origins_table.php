<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataOriginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_origins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->index();
            $table->string('data_type')->index();
            $table->string('sheet_name')->index();
            $table->string('file_name');
            $table->json('property_field');
            $table->json('data_field');
            $table->json('excel_snap');
        });

        Schema::create('data_origin_has_channel', function (Blueprint $table) {
            $table->unsignedBigInteger('data_origin_id');
            $table->unsignedBigInteger('channel_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_origins');
        Schema::dropIfExists('data_origin_has_channel');
    }
}
