<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVivoSpendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vivo_spends', function (Blueprint $table) {
            $table->bigIncrements('id');
            foreach (\App\Models\VivoSpend::$excelFields as $name => $field) {
                $table->string($field)->nullable()->comment($name);
            }
            $table->string('code')->nullable();
            $table->string('type')->nullable();
            $table->string('department_id')->nullable();

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
        Schema::dropIfExists('vivo_spends');
    }
}
