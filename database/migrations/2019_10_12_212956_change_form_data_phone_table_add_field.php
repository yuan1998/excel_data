<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFormDataPhoneTableAddField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_data_phones', function (Blueprint $table) {
            $table->boolean('has_visitor_id')->default(0);
            $table->boolean('has_url')->default(0);
            $table->string('archive_type')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_data_phones', function (Blueprint $table) {
            $table->dropColumn('has_visitor_id');
            $table->dropColumn('has_url');
            $table->dropColumn('archive_type');
        });
    }
}
