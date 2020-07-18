<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCrmDataTableAddClientNameField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('arriving_data', function (Blueprint $table) {
            $table->string('client')->nullable()->index();
        });
        Schema::table('bill_account_data', function (Blueprint $table) {
            $table->string('client')->nullable()->index();
        });
        Schema::table('customer_phones', function (Blueprint $table) {
            $table->string('client')->nullable()->index();
        });
        Schema::table('temp_customer_data', function (Blueprint $table) {
            $table->string('client')->nullable()->index();
        });



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('arriving_data', function (Blueprint $table) {
            $table->dropColumn('client');
        });
        Schema::table('bill_account_data', function (Blueprint $table) {
            $table->dropColumn('client');
        });
        Schema::table('customer_phones', function (Blueprint $table) {
            $table->dropColumn('client');
        });
        Schema::table('temp_customer_data', function (Blueprint $table) {
            $table->dropColumn('client');
        });




    }
}
