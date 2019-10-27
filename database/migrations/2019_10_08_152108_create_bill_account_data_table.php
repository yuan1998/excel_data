<?php

use App\Models\BillAccountData;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillAccountDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_account_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            foreach (BillAccountData::$excelFields as $key => $field)
            {
                $table->string($field)->comment($key)->nullable();
            }
            $table->string('type')->default('zx');
            $table->string('uuid');
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
        Schema::dropIfExists('bill_account_data');
    }
}
