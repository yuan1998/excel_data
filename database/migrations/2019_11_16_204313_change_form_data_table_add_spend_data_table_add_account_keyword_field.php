<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFormDataTableAddSpendDataTableAddAccountKeywordField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_data', function (Blueprint $table) {
            $table->string('account_keyword')->nullable();
        });
        Schema::table('spend_data', function (Blueprint $table) {
            $table->string('account_keyword')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_data', function (Blueprint $table) {
            $table->dropColumn('account_keyword');
        });
        Schema::table('spend_data', function (Blueprint $table) {
            $table->dropColumn('account_keyword');
        });

    }
}
