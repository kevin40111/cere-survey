<?php

Route::get('survey/{book_id}/{type}/page', array('before' => 'has_survey_login', 'uses' => 'SurveyController@page'));
Route::post('survey/{book_id}/{type}/getBook', 'SurveyController@getBook');
Route::post('survey/{book_id}/{type}/getNextNode', 'SurveyController@getNextNode');
Route::post('survey/{book_id}/{type}/getNextNodes', 'SurveyController@getNextNodes');
Route::post('survey/{book_id}/{type}/getChildren', 'SurveyController@getChildren');
Route::post('survey/{book_id}/{type}/getRule', 'SurveyController@getRule');
Route::post('survey/{book_id}/{type}/checkInRows', 'SurveyController@checkInRows');
Route::get('survey/{book_id}/{type}/surveyLogin', 'SurveyController@surveyLogin');

Route::get('surveyDemo/{book_id}/{type}/page', 'SurveyController@page');
Route::get('surveyDemo/{book_id}/{type}/initRepository', 'SurveyController@initRepository');
Route::post('surveyDemo/{book_id}/{type}/getBook', 'SurveyController@getBook');
Route::post('surveyDemo/{book_id}/{type}/getNextNode', 'SurveyController@getNextNode');
Route::post('surveyDemo/{book_id}/{type}/getNextNodes', 'SurveyController@getNextNodes');
Route::post('surveyDemo/{book_id}/{type}/getChildren', 'SurveyController@getChildren');
Route::post('surveyDemo/{book_id}/{type}/getRule', 'SurveyController@getRule');
Route::post('surveyDemo/{book_id}/{type}/getDemoOption', 'SurveyController@getDemoOption');
Route::get('surveyDemo/{book_id}/{type}/cleanAnswers', 'SurveyController@cleanAnswers');
Route::get('surveyDemo/{book_id}/{type}/demoLogin', 'SurveyController@demoLogin');
