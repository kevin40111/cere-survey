<?

namespace Plat\Eloquent\Survey;
use Eloquent;
use Crypt;

class SurveyBookLogin extends Eloquent {

    protected $table = 'plat_survey.dbo.file_book_login';

    public $timestamps = false;

    protected $fillable = array('file_id', 'login_id', 'new_login_id');

    protected $book_id;

    public function __construct($book_id = null, array $attributes = array())
    {

    $this->book_id = $book_id;

    parent::__construct($attributes);

    }


    public function checkForInsert($login_id)
    {   
        if(empty($this->where('file_id', $this->book_id)->where('login_id', $login_id)->first())){

            $crypt_id = Crypt::encrypt($login_id);

            $this->insert(['file_id' => $this->book_id, 'login_id' => $login_id, 'new_login_id' => $crypt_id]);
        }
    }

    public function getBookTester($login_id)
    {
        return json_encode($this->where('file_id', $this->book_id)->where('login_id', $login_id)->select(['file_id', 'new_login_id'])->first());
    }
}
