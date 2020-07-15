<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFormDataTableAddDataSnapAndChannelIdFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_data', function (Blueprint $table) {
            $table->unsignedBigInteger('channel_id')->nullable()->index();
            $table->json('data_snap')->nullable();
            $table->string('code')->nullable();
            $table->string('phone')->nullable()->index();
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
            $table->dropColumn('channel_id');
            $table->dropColumn('data_snap');
            $table->dropColumn('code');
            $table->dropColumn('phone');
        });
    }
}
