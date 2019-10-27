<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBaiduDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('baidu_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            foreach (\App\Models\BaiduData::$excelFields as $key => $value) {
                $table->string($value)->comment($key)->nullable();
            }
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
        Schema::dropIfExists('baidu_data');
    }
}
