<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveyApplication extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
    public function up()
    {
        Schema::create('survey_application', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('book_id');
			$table->integer('member_id');
			$table->boolean('extension');
			$table->integer('ext_book_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('survey_application');
    }

}
