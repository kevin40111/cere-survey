<?php

Route::filter('has_survey_login', function($route)
{
    $session = new Cere\Survey\Auth\SurveySession($route->getParameter('book_id'));

    if (! $session->exists()) {

        return Redirect::to('survey/'.$book_id.'/surveyLogin');

    }

});
