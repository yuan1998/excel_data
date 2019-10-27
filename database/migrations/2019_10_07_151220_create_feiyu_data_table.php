<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeiyuDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feiyu_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            foreach (\App\Models\FeiyuData::$excelFields as $key => $field) {
                $table->string($field)->comment($key)->nullable();
            }
            $table->string('type')->default('zx');
            $table->integer('intention')->default(0);
            $table->boolean('is_archive')->default(0);
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
        Schema::dropIfExists('feiyu_data');
    }
}
