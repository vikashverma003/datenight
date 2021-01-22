<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToBusinessEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_events', function (Blueprint $table) {
            $table->string('recurring_type')->nullable();
            $table->string('month_day')->nullable();
            $table->string('week_day_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_events', function (Blueprint $table) {
            $table->dropColumn('recurring_type');
            $table->dropColumn('month_day');
            $table->dropColumn('week_day_name');
        });
    }
}
