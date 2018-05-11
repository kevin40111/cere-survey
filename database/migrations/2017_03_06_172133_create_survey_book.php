<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveyBook extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_book', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('file_id');
            $table->integer('sheet_id');
            $table->string('title', 50);
            $table->boolean('lock', 200);
            $table->text('auth');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('survey_book');
    }

}
