<?php

namespace Plat\Survey;

use Cere\Survey\Eloquent\SurveyBookLogin;
use Session;
use Crypt;

class SurveySession
{
    public static function check($book_id)
    {
        if (!Session::has('survey_login')) {
            return false;
        }

        return $book_id == Session::get('survey_login.book_id');
    }

    public static function login($book_id, $login_id)
    {
        $surveyBookLogin = static::getSurveyBookLogin([
            'book_id' => $book_id,
            'login_id' => $login_id,
        ]);

        Session::put('survey_login', $surveyBookLogin->toArray());

        return self::getHashId();
    }

    public static function getSurveyBookLogin($attributes)
    {
        if (!is_null($instance = SurveyBookLogin::where($attributes)->first())) {
            return $instance;
        }

        $attributes['encrypt_id'] = Crypt::encrypt($attributes['login_id']);

        return SurveyBookLogin::create($attributes);
    }

    public static function getHashId()
    {
        $survey_login = Session::get('survey_login');

        return $survey_login['encrypt_id'];
    }

    public static function getLoginId()
    {
        $survey_login = Session::get('survey_login');

        return $survey_login['login_id'];
    }

    public static function logout()
    {
        Session::forget('survey_login');
    }
}