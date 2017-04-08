<?php

use Plat\Eloquent\Survey as SurveyORM;
use Plat\Survey\SurveySession;
use Plat\Survey\SurveyRepository;
use Plat\Survey\DemoRepository;
use Plat\Survey;
class SurveyController extends \BaseController {
    /**
     * init the repository of the survey.
     *
     * @param  int  $book_id
     * @param  string  $type
     * @return Response
     */
    function __construct()
    {
        $this->beforeFilter(function ($route) {
            $this->type = $route->getParameter('type');
            $book_id = $route->getParameter('book_id');

            if ($this->type == 'demo') {
                $this->user_id = Auth::user()->id;
                $this->repository = new DemoRepository($book_id);
                if (!$this->repository->exist('answers')) {

                    //$page = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->first();
                    $this->repository->increment($this->user_id, ['page_id' => null]);
                    $questions = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) {
                        return array_merge($carry, $page->getQuestions());
                    }, []);

                    array_map (function($question) {
                        $this->repository->put($this->user_id, $question['id'], null);
                    }, $questions);
                }
            }

            if ($this->type == 'survey') {
                $this->user_id = SurveySession::getHashId();
                $this->repository = new SurveyRepository($book_id);
            }
        });

    }

    /**
     * Display a page of the survey.
     *
     * @return Response
     */
    public function page()
    {
        return View::make('layout-survey')->nest('context', 'files.survey.demo-ng');
    }

    /**
     * Display a page of the survey login.
     *
     * @return Response
     */
    public function surveyLogin()
    {
        SurveySession::logout();

        return View::make('layout-survey')->nest('context', 'files.survey.surveylogin-ng');
    }

    /**
     * Display a page of the demo login.
     *
     * @return Response
     */
    public function demoLogin()
    {
        return View::make('layout-survey')->nest('context', 'files.survey.demologin-ng');
    }

    /**
     * Check if login user in population.
     *
     * @param  int  $book_id
     * @return Response
     */
    public function checkInRows($book_id)
    {
        SurveySession::logout();

        $login_id = Input::get('id') ;

        $file_book = SurveyORM\Book::find($book_id);

        $table = Files::find($file_book->rowsFile_id)->sheets->first()->tables->first();

        $in_rows  = DB::table('rows.dbo.'.$table->name)->where('C'.$file_book->loginRow_id, $login_id)->exists();

        if (!$in_rows) {
             return Redirect::to('survey/'.$book_id.'/survey/surveyLogin');
        }

        $encrypt_id = SurveySession::login($book_id, $login_id);
        //$page = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->first();
        if (!$this->repository->exist($encrypt_id)) {
            $this->repository->increment($encrypt_id, ['page_id' => null]);
        }

        return Redirect::to('survey/'.$book_id.'/survey/page');
    }

    /**
     * Show the book for survey.
     *
     * @param  int  $book_id
     * @return Response
     */
    public function getBook($book_id)
    {
        return ['book' => SurveyORM\Book::find($book_id)];
    }

    /**
     * Show a next node in book.
     *
     * @param  int  $book_id
     * @return Response
     */
    public function getNextNode($book_id)
    {
        $answers = (object)$this->repository->all($this->user_id);
        $url = null;
        $previous = is_null($answers->page_id) ? null : SurveyORM\Node::find($answers->page_id) ;//已填答頁數
        $page = is_null($previous) ? SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->first() : $previous->next;

        if (Input::get('next')) {
            //撿查是否有漏答
            //if (checkHasMissing($page->id)) {
                $this->repository->put($this->user_id, 'page_id', $page->id); //update page
            /*} else {
                return ['node' => $page->load('rule'), 'answers' => $this->repository->all($this->user_id), 'url' => $url];
            }*/
            $lastPage = is_null($page->next);
            $nextPage = $lastPage ? null : $page->next->load('rule');
        } else {
            $lastPage = is_null($page);
            $nextPage = $lastPage ? null : $page->load('rule');
        }

        if ($lastPage) {
            $extBooks = $this->getExtBook($book_id);
            $extended = (count($extBooks) == 0) ? false : true;
            if ($extended) {
                if ($this->type == 'survey') {
                    $book = SurveyORM\Book::find($book_id);
                    $rowsFile = Files::find($book->rowsFile_id)->sheets()->first()->tables()->first();
                    $userOrganization = DB::table('rows.dbo.'.$rowsFile->name)->where('C'.$book->loginRow_id, SurveySession::getLoginId())->select('C'.$book->column_id.' AS value')->first();

                    $extBook = $extBooks->filter(function ($extBook) use($userOrganization){
                        $values = array_fetch($extBook->rule->expressions[0]['conditions'], 'value');
                        return in_array($userOrganization->value, $values);
                    })->first();

                    $extBook_id = $extBook->id;

                    $encrypt_id = SurveySession::login($extBook_id, SurveySession::getLoginId());
                    if (!DB::table($extBook_id)->where('created_by', $encrypt_id)->exists()) {
                        DB::table($extBook_id)->insert(['page_id' => null, 'created_by' => $encrypt_id]);
                    }

                    $url = '/survey'.'/'.$extBook_id.'/survey/page';
                }
                if ($this->type == 'demo') {
                    $url = '/surveyDemo'.'/'.$book_id.'/demo/demoLogin';
                }
            }
        }

        return ['node' => $nextPage, 'answers' => $this->repository->all($this->user_id), 'url' => $url];
    }

    /**
     * Show nodes in a page node.
     *
     * @return Response
     */
    public function getNextNodes()
    {
        $nodes = SurveyORM\Node::find(Input::get('page.id'))->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions.rule', 'answers.rule', 'rule']);

        return ['nodes' => $nodes];
    }

    /**
     * Show children nodes.
     *
     * @return Response
     */
    public function getChildren($book_id)
    {
        if (Input::has('parent')) {
            $class = Input::get('parent.class');
            $nodes = $class::find(Input::get('parent.id'))->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions.rule', 'answers.rule', 'rule']);
        } else {
            $nodes = [];
        }

        Input::has('value') && $this->repository->put($this->user_id, Input::get('question.id'), Input::get('value'));

        return ['nodes' => $nodes];
    }

    /**
     * Get rules.
     *
     * @return Response
     */
    /*public function getRule()
    {
        $class = Input::get('skipTarget.class');
        $root = $class::find(Input::get('skipTarget.id'));

        $rule = Survey\RuleRepository::target($root)->getRule();

        return ['rule' => $rule];
    }*/

    /**
     * Clean demo answers .
     *
     * @param  int  $book_id
     * @return Response
     */
    public function cleanAnswers($book_id)
    {
        $this->repository->decrement($this->user_id);

        return Redirect::to('surveyDemo/'.$book_id.'/demo/page');
    }

    /**
     * get extend books .
     *
     * @param  int  $book_id
     * @return Response
     */
    public function getExtBook($book_id)
    {
        return SurveyORM\Book::find($book_id)->applications->filter(function ($application) {
            return SurveyORM\Book::find($application->ext_book_id)->rule()->exists() && $application->extension ;
        })->map(function ($application) {
            return SurveyORM\Book::find($application->ext_book_id)->load('rule');
        });
    }

    /**
     * get demo options .
     *
     * @param  int  $book_id
     * @return Response
     */
    public function getDemoOption($book_id)
    {
        $options = [];
        foreach ($this->getExtBook($book_id)  as $extBook) {
            $values = array_fetch($extBook->rule->expressions[0]['conditions'], 'value');

            foreach ($values  as $value) {
                $option = [];
                $option['ext_book_id'] = $extBook->id;
                $option['organization_id'] = $value;
                $option['name'] = \Plat\Project\OrganizationDetail::where('id',$value)->orderBy('year', 'desc')->select('name')->first()->name;
                array_push($options,$option);
            }
        }

        return ['options' => $options];
    }

    /**
     * Compare rules.
     *
     * @return Response
     */
    public function compareRule($rule_id,$answer)
    {

        $status = Survey\RuleRepository::target($rule_id)->compareRule($rule_id,$answer);

        return $status;
    }
}
