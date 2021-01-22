<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialEventImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('special_event_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('special_event_id');
            $table->string('file_name',512)->nullable();
            $table->string('active')->default(1);
            $table->timestamps();
            $table->foreign('special_event_id')->references('id')->on('special_events');
    
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('special_event_images');
    }
}
