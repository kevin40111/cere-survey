<?php

namespace Cere\Survey\Auth;

use \Files;
use Cere\Survey\Field\FieldRepository;
use Cere\Survey\SurveySession;

class FieldUser
{
    public $id;

    protected $logined;

    function __construct($book)
    {
        $this->book = $book;
        $this->logined = SurveySession::check($this->book->id);
        $this->id = SurveySession::getHashId($this->book->id);
    }

    public function login($userinfo)
    {
        SurveySession::logout();

        $table = Files::find($this->book->rowsFile_id)->sheets->first()->tables->first();

        $in_rows  = FieldRepository::target($table, $this->book->file->created_by)->rowExists(['C'.$this->book->loginRow_id => $userinfo]);

        $this->logined = $in_rows || $this->book->no_population;

        if ($this->logined) {
            $this->id = SurveySession::login($this->book->id, $userinfo);
        }
    }

    public function logout()
    {
        SurveySession::logout();
    }

    public function logined()
    {
        return $this->logined;
    }
}
