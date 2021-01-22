<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTemDeleteColumnsToBusinessEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_events', function (Blueprint $table) {
            $table->string('is_delete')->default(0);
            $table->string('delete_till_date')->nullable();
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
           $table->dropColumn('is_delete');
           $table->dropColumn('delete_till_date');
        });
    }
}
