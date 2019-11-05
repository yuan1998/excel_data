<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAccountDataTableAddIsDefault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_data', function (Blueprint $table) {
            $table->boolean('is_default')->default(0);
            $table->string('type')->default('zx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_data', function (Blueprint $table) {
            $table->dropColumn('is_default');
            $table->dropColumn('type');
        });
    }
}
