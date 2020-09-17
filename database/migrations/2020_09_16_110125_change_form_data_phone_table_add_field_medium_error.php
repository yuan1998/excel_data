<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFormDataPhoneTableAddFieldMediumError extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_data_phones', function (Blueprint $table) {
            $table->integer('medium_error')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *12
     * @return void
     */
    public function down()
    {
        Schema::table('form_data_phones', function (Blueprint $table) {
            $table->dropColumn('medium_error');
        });
    }
}
