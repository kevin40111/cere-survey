<?php

namespace Plat\Surveys;
use Plat\Eloquent\Survey\SurveyBookLogin as SurveyBookLogin;
use Session;
use DB;

class SurveySession {

    public function setSession($book_id, $input_id)
    {
        $survey_login = new SurveyBookLogin($book_id);

        $sesion_push = $survey_login->getBookTester($input_id);

        Session::put('survey_login_id', $sesion_push);
    }

    /**
     * @todo check is logined
     */
    public static function check($book_id)
    {

    }

    /**
     * @todo set session and return hash_id
     * @return string
     */
    public static function login($book_id, $login_id)
    {

        return self::getHashId($login_id);
    }

    /**
     * @todo encrypt login_id to hash_id
     * @return string
     */
    public static function getHashId($login_id)
    {

    }

    /**
     * @todo forget session
     */
    public static function logout()
    {

    }

}