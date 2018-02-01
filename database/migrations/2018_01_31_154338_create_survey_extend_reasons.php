<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveyExtendReasons extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_extend_reasons', function (Blueprint $table) {
            $table->increments('id');
            $table->text('content');
            $table->integer('apply_id');
            $table->integer('verify_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('survey_extend_reasons');
    }

}
