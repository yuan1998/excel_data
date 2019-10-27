<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTempCustomerDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_customer_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            foreach (\App\Models\TempCustomerData::$excelFields as $value) {
                $table->string($value)->nullable();
            }
            $table->string('customer_id');
            $table->string('medium_id');
            $table->string('archive_id');
            $table->string('type');
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
        Schema::dropIfExists('temp_customer_data');
    }
}
