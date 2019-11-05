<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeArchiveTypesTableAddDataIdParentIdLevelField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('archive_types', function (Blueprint $table) {
            $table->string('data_id')->nullable();
            $table->string('data_parent_id')->nullable();
            $table->string('level')->default('1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('archive_types', function (Blueprint $table) {
            $table->dropColumn('data_id');
            $table->dropColumn('data_parent_id');
            $table->dropColumn('level');
        });
    }
}
