<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveyFields extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('table_id');
            $table->string('name', 50);
            $table->string('title', 500);
            $table->string('rules', 50);
            $table->boolean('unique');
            $table->boolean('encrypt');
            $table->boolean('isnull');
            $table->boolean('readonly');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('survey_fields');
    }

}
