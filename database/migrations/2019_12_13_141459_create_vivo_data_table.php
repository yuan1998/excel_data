<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVivoDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vivo_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            foreach (\App\Models\VivoData::$excelFields as $key => $value) {
                $table->string($value)->nullable()->comment($key);
            }
            $table->dateTime('date')->nullable();
            $table->string('code')->nullable();
            $table->string('type')->nullable();
            $table->string('form_type')->nullable();
            $table->string('department_id')->nullable();
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
        Schema::dropIfExists('vivo_data');
    }
}
