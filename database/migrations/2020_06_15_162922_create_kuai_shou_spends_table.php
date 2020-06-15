<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKuaiShouSpendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kuai_shou_spends', function (Blueprint $table) {
            $table->bigIncrements('id');

            foreach (\App\Models\KuaiShouSpend::$fields as $fieldText => $field) {
                $table->string($field)->nullable()->comment($fieldText);
            }

            $table->string('type');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('code');

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
        Schema::dropIfExists('kuai_shou_spends');
    }
}
