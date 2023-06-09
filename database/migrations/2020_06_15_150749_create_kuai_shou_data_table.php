<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKuaiShouDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kuai_shou_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            foreach (\App\Models\KuaiShouData::$fields as $fieldName => $field) {
                $table->string($field)->nullable()->comment($fieldName);
            }
            $table->dateTime('date')->nullable();
            $table->string('code')->nullable();
            $table->string('type');
            $table->integer('form_type');
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
        Schema::dropIfExists('kuai_shou_data');
    }
}
