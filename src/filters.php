<?php

Route::filter('has_survey_login', function($route)
{
    $book_id = $route->getParameter('book_id');

    if (! Cere\Survey\SurveySession::check($book_id)) {

        return Redirect::to('survey/'.$book_id.'/surveyLogin');

    }

});
