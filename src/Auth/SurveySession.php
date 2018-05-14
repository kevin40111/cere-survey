<?php

namespace Cere\Survey\Auth;

use Session;

class SurveySession
{
    protected static $name = 'survey_login';

    function __construct($book_id)
    {
        $this->book_id = $book_id;
    }

    public static function create($book_id, $userinfo)
    {
        Session::put(self::$name.'.'.$book_id, [
            'userinfo' => $userinfo,
            'encrypt' => hash('sha256', $userinfo),
        ]);

        return new self($book_id);
    }

    public function exists()
    {
        return Session::has(self::$name.'.'.$this->book_id);
    }

    public function userinfo()
    {
        return Session::get(self::$name.'.'.$this->book_id.'.userinfo');
    }

    public function encrypt()
    {
        return Session::get(self::$name.'.'.$this->book_id.'.encrypt');
    }

    public function destroy()
    {
        Session::forget(self::$name.'.'.$this->book_id);
    }
}
