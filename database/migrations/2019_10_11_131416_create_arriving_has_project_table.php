<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArrivingHasProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('arriving_has_project', function (Blueprint $table) {
            $table->unsignedBigInteger('arriving_id');
            $table->unsignedBigInteger('project_id');

            $table->foreign('arriving_id')
                ->references('id')
                ->on('arriving_data')
                ->onDelete('CASCADE');

            $table->foreign('project_id')
                ->references('id')
                ->on('project_types')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('arriving_has_project');
    }
}
