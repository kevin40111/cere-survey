<?php

Route::group(array('prefix' => 'survey'), function() {
    Route::get('{book_id}/page', array('before' => 'has_survey_login', 'uses' => 'SurveyController@page'));
    Route::post('{book_id}/getBook', 'SurveyController@getBook');
    Route::post('{book_id}/getPage', 'SurveyController@getPage');
    Route::post('{book_id}/nextPage', 'SurveyController@nextPage');
    Route::post('{book_id}/getNodes', 'SurveyController@getNodes');
    Route::post('{book_id}/saveAnswer', 'SurveyController@saveAnswer');
    Route::post('{book_id}/getChildrens', 'SurveyController@getChildrens');
    Route::post('{book_id}/getRule', 'SurveyController@getRule');
    Route::post('{book_id}/login', 'SurveyController@login');
    Route::get('{book_id}/surveyLogin', 'SurveyController@surveyLogin');
});

Route::group(array('prefix' => 'surveyDemo'), function() {
    Route::get('{book_id}/page', 'SurveyController@page');
    Route::post('{book_id}/getBook', 'SurveyController@getBook');
    Route::post('{book_id}/getPage', 'SurveyController@getPage');
    Route::post('{book_id}/nextPage', 'SurveyController@nextPage');
    Route::post('{book_id}/getNodes', 'SurveyController@getNodes');
    Route::post('{book_id}/saveAnswer', 'SurveyController@saveAnswer');
    Route::post('{book_id}/getChildrens', 'SurveyController@getChildrens');
    Route::post('{book_id}/getRule', 'SurveyController@getRule');
    Route::post('{book_id}/getDemoOption', 'SurveyController@getDemoOption');
    Route::get('{book_id}/cleanAnswers', 'SurveyController@cleanAnswers');
    Route::get('{book_id}/demoLogin', 'SurveyController@demoLogin');
});