<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropAccountReturnPointsTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('account_return_points');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('account_return_points', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('rebate');
            $table->string('keyword')->nullable();
            $table->integer('form_type');
            $table->boolean('is_default')->default(0);
            $table->timestamps();
        });
    }
}
