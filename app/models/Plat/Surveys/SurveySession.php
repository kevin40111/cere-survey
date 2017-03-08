<?php

namespace Plat\Surveys;

use Plat\Eloquent\Survey\SurveyBookLogin as SurveyBookLogin;
use Session;
use DB;

class SurveySession {

    public static function check($book_id)
    {
        if (Session::has('survey_login_data')) {

            $session_data    = Session::get('survey_login_data');

            return $book_id == $session_data['login_book'];

        }

        return false;
    }


    public static function login($book_id, $login_id)
    {
        $survey_login_table = new SurveyBookLogin($book_id);

        $login_data   = $survey_login_table->getBookTester($login_id);

        $session_put  = array(

            'login_book' => $login_data['file_id'],

            'login_id'   => $login_data['new_login_id']

            );

        Session::put('survey_login_data', $session_put);

        return self::getHashId($login_id);
    }


    public static function getHashId()
    {   
        $session_data = Session::get('survey_login_data');

        return $session_data['login_id']; 
    }


    public static function logout()
    {
        Session::forget('survey_login_data');
    }
}