<?php

namespace Cere\Survey\Eloquent;

use Eloquent;

class SurveyBookLogin extends Eloquent
{
    protected $table = 'file_book_login';

    public $timestamps = false;

    protected $fillable = ['book_id', 'login_id', 'encrypt_id'];
}
