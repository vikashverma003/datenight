<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOthersColumnToBusinessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('name')->after('username')->nullable();
            $table->string('opening_time')->nullable();
            $table->longText('closing_time')->nullable();
            $table->text('description')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
                $table->dropColumn('name');
                $table->dropColumn('opening_time');
                $table->dropColumn('closing_time');
                $table->dropColumn('description');
        });
    }
}
