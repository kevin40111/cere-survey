<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveyRuleSkipers extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_rule_skipers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('effect_type', 200);
            $table->integer('effect_id');
            $table->string('type', 50);
            $table->integer('page_id');
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
        Schema::drop('survey_rule_skipers');
    }

}
