<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class createSurveyRuleOperations extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('survey_rule_operations', function(Blueprint $table)
		{
            $table->increments('id');
            $table->string('operator', 50);
            $table->string('target_type', 200);
            $table->integer('target_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('survey_rule_operations');
	}

}
