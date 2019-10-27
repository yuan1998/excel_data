<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBaiduSpendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('baidu_spends', function (Blueprint $table) {
            $table->bigIncrements('id');
            foreach (\App\Models\BaiduSpend::$excelFields as $key => $field) {
                $table->string($field)->comment($key)->nullable();
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
        Schema::dropIfExists('baidu_spends');
    }
}
