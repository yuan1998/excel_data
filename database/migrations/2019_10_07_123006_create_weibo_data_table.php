<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeiboDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weibo_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            foreach (\App\Models\WeiboData::$excelFields as $key => $field) {
                $table->string($field)->comment($key)->nullable();
            }
            $table->boolean('is_archive')->default(0);
            $table->integer('intention')->default(0);
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
        Schema::dropIfExists('weibo_data');
    }
}
