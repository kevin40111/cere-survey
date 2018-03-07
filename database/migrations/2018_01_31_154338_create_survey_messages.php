<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveyMessages extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->increments('user_id');
            $table->text('content');
            $table->text('title');
            $table->string('target_type');
            $table->integer('target_id');
            $table->timestamp();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('survey_messages');
    }

}
