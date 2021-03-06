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
            $table->integer('node_id');
            $table->string('effect_type', 200);
            $table->integer('effect_id');
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
