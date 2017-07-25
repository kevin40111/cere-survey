<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveyApplicableOptions extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_applicable_options', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('book_id');
            $table->integer('survey_applicable_option_id');
            $table->string('survey_applicable_option_type', 50);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('survey_applicable_options');
    }

}
