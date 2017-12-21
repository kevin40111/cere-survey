<?php

namespace Cere\Survey\Auth;

use \Files;
use Cere\Survey\Field\FieldRepository;

class FieldUser
{
    public $id;

    protected $logined;

    function __construct($book)
    {
        $this->book = $book;
        $this->session = new SurveySession($book->id);
        $this->id = $this->session->encrypt();
    }

    public function login($userinfo)
    {
        $this->logout();

        $table = Files::find($this->book->rowsFile_id)->sheets->first()->tables->first();

        $in_rows  = FieldRepository::target($table, $this->book->file->created_by)->rowExists(['C'.$this->book->loginRow_id => $userinfo]);

        if ($in_rows || $this->book->no_population) {
            $this->session = SurveySession::create($this->book->id, $userinfo);
            $this->id = $this->session->encrypt();
        }
    }

    public function logout()
    {
        $this->session->destroy();
    }

    public function logined()
    {
        return $this->session->exists();
    }
}
