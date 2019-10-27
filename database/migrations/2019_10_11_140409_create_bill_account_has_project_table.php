<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillAccountHasProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_account_has_project', function (Blueprint $table) {
            $table->unsignedBigInteger('bill_account_id');
            $table->unsignedBigInteger('project_id');

            $table->foreign('bill_account_id')
                ->references('id')
                ->on('bill_account_data')
                ->onDelete('CASCADE');

            $table->foreign('project_id')
                ->references('id')
                ->on('project_types')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bill_account_has_project');
    }
}
