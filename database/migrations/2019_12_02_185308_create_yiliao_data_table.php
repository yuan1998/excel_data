<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYiliaoDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('yiliao_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            foreach (\App\Models\YiliaoData::$excelFields as $key => $value) {
                $table->string($value)->comment($key)->nullable();
            }
            $table->string('type')->default('zx');
            $table->string('code')->nullable();
            $table->string('form_type')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
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
        Schema::dropIfExists('yiliao_data');
    }
}
