<?php

Route::group(array('prefix' => 'survey'), function() {
    Route::get('{book_id}/page', array('before' => 'has_survey_login', 'uses' => 'SurveyController@page'));
    Route::post('{book_id}/getBook', 'SurveyController@getBook');
    Route::post('{book_id}/getNextNode', 'SurveyController@getNextNode');
    Route::post('{book_id}/getNextNodes', 'SurveyController@getNextNodes');
    Route::post('{book_id}/getChildren', 'SurveyController@getChildren');
    Route::post('{book_id}/getRule', 'SurveyController@getRule');
    Route::post('{book_id}/login', 'SurveyController@login');
    Route::get('{book_id}/surveyLogin', 'SurveyController@surveyLogin');
});

Route::group(array('prefix' => 'surveyDemo'), function() {
    Route::get('surveyDemo/{book_id}/page', 'SurveyController@page');
    Route::get('surveyDemo/{book_id}/initRepository', 'SurveyController@initRepository');
    Route::post('surveyDemo/{book_id}/getBook', 'SurveyController@getBook');
    Route::post('surveyDemo/{book_id}/getNextNode', 'SurveyController@getNextNode');
    Route::post('surveyDemo/{book_id}/getNextNodes', 'SurveyController@getNextNodes');
    Route::post('surveyDemo/{book_id}/getChildren', 'SurveyController@getChildren');
    Route::post('surveyDemo/{book_id}/getRule', 'SurveyController@getRule');
    Route::post('surveyDemo/{book_id}/getDemoOption', 'SurveyController@getDemoOption');
    Route::get('surveyDemo/{book_id}/cleanAnswers', 'SurveyController@cleanAnswers');
    Route::get('surveyDemo/{book_id}/demoLogin', 'SurveyController@demoLogin');
});