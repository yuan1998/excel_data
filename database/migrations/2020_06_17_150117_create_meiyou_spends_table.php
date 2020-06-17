<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeiyouSpendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meiyou_spends', function (Blueprint $table) {
            $table->bigIncrements('id');
            foreach (\App\Models\MeiyouSpend::$fields as $fieldText => $field) {
                $table->string($field)->nullable()->comment($fieldText);
            }

            $table->unsignedBigInteger('department_id');
            $table->string('type');
            $table->string('code')->nullable();
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
        Schema::dropIfExists('meiyou_spends');
    }
}
