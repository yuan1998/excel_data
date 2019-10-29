<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('rebate');
            $table->string('keyword');
            $table->string('crm_keyword');
            $table->unsignedBigInteger('channel_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_data');
    }
}
