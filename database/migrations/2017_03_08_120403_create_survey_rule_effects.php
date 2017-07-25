<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveyRuleEffects extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_rule_effects', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rule_id');
            $table->string('survey_rule_effect_type', 50);
            $table->integer('survey_rule_effect_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('survey_rule_effects');
    }

}
