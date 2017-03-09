<?php

namespace Plat\Eloquent\Survey;

use Eloquent;

class SurveyBookLogin extends Eloquent
{
    protected $table = 'plat_survey.dbo.file_book_login';

    public $timestamps = false;

    protected $fillable = ['book_id', 'login_id', 'encrypt_id'];
}
