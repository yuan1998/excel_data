<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCrmDataTablesCommentFieldToTextType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_customer_data', function (Blueprint $table) {
            $table->text('comment')->change();
        });

        Schema::table('arriving_data', function (Blueprint $table) {
            $table->text('comment')->change();
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('temp_customer_data', function (Blueprint $table) {
            $table->string('comment')->change();
        });
        Schema::table('arriving_data', function (Blueprint $table) {
            $table->string('comment')->change();
        });

    }
}
