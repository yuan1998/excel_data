<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepartmentTypeTableAddKeywordField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('department_types', function (Blueprint $table) {
            $table->string('keyword')->nullable();
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
        Schema::table('department_types', function (Blueprint $table) {
            $table->dropColumn('keyword');
            $table->dropColumn('type');
        });
    }
}
