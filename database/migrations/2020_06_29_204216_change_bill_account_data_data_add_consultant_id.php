<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeBillAccountDataDataAddConsultantId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bill_account_data', function (Blueprint $table) {
            $table->unsignedBigInteger('online_return_visit_by_id')->index()->nullable();
            $table->foreign('online_return_visit_by_id')
                ->references('id')
                ->on('consultants')
                ->onDelete('set null');
            $table->unsignedBigInteger('account_by_id')->index()->nullable();
            $table->foreign('account_by_id')
                ->references('id')
                ->on('consultants')
                ->onDelete('set null');
            $table->unsignedBigInteger('online_customer_id')->index()->nullable();
            $table->foreign('online_customer_id')
                ->references('id')
                ->on('consultants')
                ->onDelete('set null');
            $table->unsignedBigInteger('archive_by_id')->index()->nullable();
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
        Schema::table('bill_account_data', function (Blueprint $table) {

            $table->dropForeign(['online_return_visit_by_id']);
            $table->dropColumn('online_return_visit_by_id');

            $table->dropForeign(['account_by_id']);
            $table->dropColumn('account_by_id');

            $table->dropForeign(['online_customer_id']);
            $table->dropColumn('online_customer_id');

            $table->dropForeign(['archive_by_id']);
            $table->dropColumn('archive_by_id');
        });
    }
}
