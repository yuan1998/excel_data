<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeArrivingDataAddIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('arriving_data', function (Blueprint $table) {
            $table->index(['is_transaction']);
            $table->index(['reception_date']);
            $table->index(['customer_status']);
            $table->index(['again_arriving']);
            $table->index(['customer_id']);
            $table->index(['archive_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('arriving_data', function (Blueprint $table) {
            $table->dropIndex(['is_transaction']);
            $table->dropIndex(['reception_date']);
            $table->dropIndex(['customer_status']);
            $table->dropIndex(['again_arriving']);
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['archive_type']);
        });
    }
}
