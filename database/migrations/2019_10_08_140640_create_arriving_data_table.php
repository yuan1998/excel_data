<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArrivingDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('arriving_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            foreach (\App\Models\ArrivingData::$excelFields as $key => $field) {
                $table->string($field)->comment($key)->nullable();
            }
            $table->string('uuid');
            $table->string('type')->default('zx');
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
        Schema::dropIfExists('arriving_data');
    }
}
