<?php

namespace Plat\Eloquent\Survey;

use Eloquent;
use DB;
use Crypt;
use Plat\Eloquent\Survey as SurveyORM;

class SurveyBookLogin extends Eloquent
{
    protected $table = 'plat_survey.dbo.file_book_login';

    public $timestamps = false;

    protected $fillable = array('file_id', 'login_id', 'new_login_id');

    protected $book_id;

    public function __construct($book_id = null, array $attributes = array())
    {
        $this->book_id = $book_id;

        parent::__construct($attributes);
    }

    public function checkHasInsert($login_id)
    {
        if ($this->where('file_id', $this->book_id)->where('login_id', $login_id)->exists()) {
            return true;
        }

        $crypt_id = Crypt::encrypt($login_id);

        $page = SurveyORM\Book::find($this->book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->first();

        $this->insert(['file_id' => $this->book_id, 'login_id' => $login_id, 'new_login_id' => $crypt_id]);

        DB::table($this->book_id)->insert(['created_by' => $crypt_id, 'page_id' => $page->id]);

        return false;
    }

    public function getBookTester($login_id)
    {
        return $this->where('file_id', $this->book_id)->where('login_id', $login_id)->select(['file_id', 'new_login_id'])->first();
    }
}
