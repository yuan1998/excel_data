<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeBaiduDataTableAddFormTypeAndDepartmentId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('baidu_data', function (Blueprint $table) {
            $table->string('form_type')->nullable();
            $table->string('code')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('baidu_data', function (Blueprint $table) {
            $table->dropColumn('form_type');
            $table->dropColumn('code');
            $table->dropColumn('department_id');
        });
    }
}
