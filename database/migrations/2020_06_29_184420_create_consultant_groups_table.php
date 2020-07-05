<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consultant_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->index();
            $table->string('type', 4)->index();
            $table->timestamps();
        });

        Schema::create('consultant_group_id', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->index();
            $table->unsignedBigInteger('consultant_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consultant_groups');
        Schema::dropIfExists('consultant_group_id');
    }
}
