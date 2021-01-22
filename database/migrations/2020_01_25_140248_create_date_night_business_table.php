<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDateNightBusinessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('date_night_business', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('date_night_id');
            $table->unsignedBigInteger('business_id');
            $table->timestamps();
            $table->foreign('date_night_id')->references('id')->on('date_nights');
            $table->foreign('business_id')->references('id')->on('businesses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('date_night_business');
    }
}
