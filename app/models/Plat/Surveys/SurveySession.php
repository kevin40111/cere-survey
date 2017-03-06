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
}