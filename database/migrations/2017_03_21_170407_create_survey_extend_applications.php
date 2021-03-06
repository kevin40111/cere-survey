<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveyExtendApplications extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_extend_applications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('hook_id');
            $table->integer('book_id');
            $table->integer('member_id');
            $table->boolean('agree');
            $table->tinyint('status');
            $table->int('step')->default(0);
            $table->text('fields');
            $table->text('individual_status');
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
        Schema::drop('survey_extend_applications');
    }

}
