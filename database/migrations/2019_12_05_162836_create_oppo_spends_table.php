<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOppoSpendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oppo_spends', function (Blueprint $table) {
            $table->bigIncrements('id');
            foreach (\App\OppoSpend::$excelFields as $key => $value) {
                $table->string($value)->nullable()->comment($key);
            }
            $table->string('spend_type')->nullable();
            $table->string('code')->nullable();
            $table->string('type')->nullable();
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
        Schema::dropIfExists('oppo_spends');
    }
}
