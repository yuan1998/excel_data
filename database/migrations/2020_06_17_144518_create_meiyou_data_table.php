<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeiyouDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meiyou_data', function (Blueprint $table) {
            $table->bigIncrements('id');

            foreach (\App\Models\MeiyouData::$fields as $fieldText => $field) {
                $table->string($field)->nullable()->comment($fieldText);
            }

            $table->unsignedBigInteger('department_id');
            $table->dateTime('date');
            $table->string('type');
            $table->json('question_data')->nullable();
            $table->string('code')->nullable();
            $table->integer('form_type');

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
        Schema::dropIfExists('meiyou_data');
    }
}
