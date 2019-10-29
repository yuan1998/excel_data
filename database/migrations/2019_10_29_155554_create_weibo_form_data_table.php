<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeiboFormDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weibo_form_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            foreach (\App\Models\WeiboData::$excelFields as $key => $field) {
                $table->string($field)->comment($key)->nullable();
            }
            $table->string('remark')->nullable();
            $table->unsignedBigInteger('weibo_user_id')->nullable();
            $table->dateTime('upload_date');
            $table->dateTime('recall_date')->nullable();
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
        Schema::dropIfExists('weibo_form_data');
    }
}
