<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCustomerPhoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_phones', function (Blueprint $table) {
            $table->string('type')->nullable();
        });
        Schema::dropIfExists('customer_phone_data');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_phones', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::create('customer_phone_data', function (Blueprint $table) {
            $table->string('type');
        });

    }
}
