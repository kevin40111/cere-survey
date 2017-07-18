<?php

use Cere\Survey\Eloquent as SurveyORM;
use Cere\Survey\SurveySession;
use Cere\Survey\SurveyRepositoryInterface;
use Cere\Survey;
use Plat\Survey\Writer\Fill;

class SurveyController extends \BaseController {
    /**
     * init the repository of the survey.
     *
     * @param  int  $book_id
     * @param  string  $type
     * @return Response
     */
    function __construct(SurveyRepositoryInterface $repository)
    {
        $this->user_id = $repository->getId();
        $this->type = $repository->getType();
        $this->repository = $repository;
    }

    /**
     * Display a page of the survey.
     *
     * @return Response
     */
    public function page()
    {
        return View::make('survey::layout-survey')->nest('context', 'survey::demo-ng');
    }

    /**
     * Display a page of the survey login.
     *
     * @return Response
     */
    public function surveyLogin()
    {
        SurveySession::logout();

        return View::make('survey::layout-survey')->nest('context', 'survey::surveylogin-ng');
    }

    /**
     * Display a page of the demo login.
     *
     * @return Response
     */
    public function demoLogin()
    {
        return View::make('survey::layout-survey')->nest('context', 'survey::demologin-ng');
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

        $login_id = Input::get('id');

        $file_book = SurveyORM\Book::find($book_id);

        $table = Files::find($file_book->rowsFile_id)->sheets->first()->tables->first();

        $in_rows  = DB::table('rows.dbo.'.$table->name)->where('C'.$file_book->loginRow_id, $login_id)->exists();

        if (!$in_rows) {
            if ($file_book->no_population) {
                $user_id = Files::find($file_book->rowsFile_id)->created_by;
                $current_time = Carbon\Carbon::now()->toDateTimeString();
                $query = DB::table($table->database . '.dbo.' . $table->name);
                $query->insert(['C'.$file_book->loginRow_id => $login_id, 'file_id' => $file_book->rowsFile_id, 'updated_at' => $current_time, 'created_at' => $current_time, 'updated_by' => $user_id, 'created_by' => $user_id]);
            } else {
                return Redirect::to('survey/'.$book_id.'/survey/surveyLogin')->withErrors(['fail' => '! 登入資料不在名單內']);
            }
        }

        $encrypt_id = SurveySession::login($book_id, $login_id);
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
        $missings = [];
        $answers = $this->repository->all($this->user_id);
        $firstPage = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->first();
        $page = $answers->page_id ? SurveyORM\Node::find($answers->page_id)->next : $firstPage;

        if (Input::get('next') && count($missings = $this->checkPage($page, $answers)) == 0) {
            $nextPage = $page->next ? $this->checkAndJump($page->next, $answers) : NULL;
            $complete = $nextPage ? $nextPage->previous : $page;
            $this->repository->put($this->user_id, 'page_id', $complete->id);
        } else {
            $nextPage = $page;
        }

        $lastPage = is_null($nextPage);
        $nextPage && $nextPage->load('rule');
        $url = $lastPage ? $this->getNextUrl($book_id) : NULL;

        return ['node' => $nextPage, 'answers' => $answers, 'url' => $url, 'missings' => $missings];
    }

    private function getNextUrl($book_id)
    {
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

                return '/survey'.'/'.$extBook_id.'/survey/page';
            }
            if ($this->type == 'demo') {
                return '/surveyDemo'.'/'.$book_id.'/demo/demoLogin';
            }
        }
    }

    private function checkPage($page, $answers)
    {
        $questions = $page->getQuestions();

        $missings = array_filter($questions, function ($question) use ($answers) {
            return ! isset($answers->{$question['id']});
        });

        return array_values($missings);
    }

    private function checkAndJump($page, $answers)
    {
        $questions = $page->getQuestions();

        $skips = 0;
        foreach ($questions as $question) {
            if (isset($answers->{$question['id']}) && $answers->{$question['id']} == -8) {
                $skips++;
            }
        }

        return $skips == sizeof($questions)
            ? $page->next ? $this->checkAndJump($page->next, $answers) : NULL
            : $page;
    }

    /**
     * Show nodes in a page node.
     *
     * @return Response
     */
    public function getNextNodes()
    {
        $nodes = SurveyORM\Node::find(Input::get('page.id'))->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions.rule', 'answers.rule', 'rule']);
        // Fill::answers($answers)

        return ['nodes' => $nodes];
    }

    /**
     * Show children nodes.
     *
     * @return Response
     */
    public function getChildren($book_id)
    {
        $question = SurveyORM\Question::find(Input::get('question.id'));
        $answers = $this->repository->all(SurveySession::getHashId());
        $filler = Fill::answers($answers)->node($question->node);

        if (Input::has('value')) {
            $filler->set($question, Input::get('value'));
            if (!empty($filler->messages)) {
                return ;
            }

            foreach ($filler->getDirty() as $id => $value) {
                $this->repository->put(SurveySession::getHashId(), $id, $value);
            }
        }

        return ['nodes' => $filler->childrens($question), 'answers' => $this->repository->all(SurveySession::getHashId()), 'message' => $filler->messages, 'logs' => DB::getQueryLog()];
    }

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
}
