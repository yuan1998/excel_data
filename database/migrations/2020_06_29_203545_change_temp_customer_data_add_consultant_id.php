<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTempCustomerDataAddConsultantId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_customer_data', function (Blueprint $table) {
            $table->unsignedBigInteger('online_customer_id')->index()->nullable();
            $table->unsignedBigInteger('return_visit_by_id')->index()->nullable();
            $table->unsignedBigInteger('archive_by_id')->index()->nullable();

            $table->foreign('online_customer_id')
                ->references('id')
                ->on('consultants')
                ->onDelete('set null');
            $table->foreign('return_visit_by_id')
                ->references('id')
                ->on('consultants')
                ->onDelete('set null');
            $table->foreign('archive_by_id')
                ->references('id')
                ->on('consultants')
                ->onDelete('set null');
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

            $table->dropForeign(['online_customer_id']);
            $table->dropForeign(['return_visit_by_id']);
            $table->dropForeign(['archive_by_id']);
            
            $table->dropColumn('online_customer_id');
            $table->dropColumn('return_visit_by_id');
            $table->dropColumn('archive_by_id');
        });
    }
}
