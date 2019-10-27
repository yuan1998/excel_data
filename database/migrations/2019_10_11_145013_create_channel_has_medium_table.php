<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelHasMediumTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_has_medium', function (Blueprint $table) {
            $table->unsignedBigInteger('channel_id');
            $table->unsignedBigInteger('medium_id');

            $table->foreign('channel_id')
                ->references('id')
                ->on('channels')
                ->onDelete('CASCADE');

            $table->foreign('medium_id')
                ->references('id')
                ->on('medium_types')
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
        Schema::dropIfExists('channel_has_medium');
    }
}
