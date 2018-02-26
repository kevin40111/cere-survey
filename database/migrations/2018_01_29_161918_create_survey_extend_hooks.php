<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveyExtendHooks extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_extend_hooks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('book_id');
            $table->text('options');
            $table->text('consent');
            $table->text('due');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('survey_extend_hooks');
    }

}
