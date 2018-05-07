<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveyRuleFactors extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('survey_rule_factors', function(Blueprint $table)
		{
            $table->increments('id');
            $table->string('target_type', 200);
            $table->integer('target_id');
            $table->string('value', 200);
            $table->integer('operation_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('survey_rule_factors');
	}

}
