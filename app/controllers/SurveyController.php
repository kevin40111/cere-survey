<?php

use Plat\Eloquent\Survey as SurveyORM;
use Plat\Survey\SurveySession;
use Plat\Survey\SurveyRepository;
use Plat\Survey\DemoRepository;

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
            $type = $route->getParameter('type');
            $book_id = $route->getParameter('book_id');

            if ($type == 'demo') {
                $this->user_id = Auth::user()->id;
                $this->repository = new DemoRepository($book_id);
                if (!$this->repository->exist('answers')) {

                    $page = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->first();
                    $this->repository->increment($this->user_id, ['page_id' => $page->id]);
                    $questions = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) {
                        return array_merge($carry, $page->getQuestions());
                    }, []);

                    array_map (function($question) {
                        $this->repository->put($this->user_id, $question['id'], null);
                    }, $questions);
                }
            }

            if ($type == 'survey') {
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
        $page = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->first();
        if (!$this->repository->exist($encrypt_id)) {
            $this->repository->increment($encrypt_id, ['page_id' => $page->id]);
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
        $previous = SurveyORM\Node::find($answers->page_id);
        $page = Input::get('next') ? $previous->next : $previous;
        $this->repository->put($this->user_id, 'page_id', $page->id);

        return ['node' => $page->load(['rule']), 'answers' => $this->repository->all($this->user_id)];
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
    public function getRule()
    {
        $class = Input::get('skipTarget.class');
        $root = $class::find(Input::get('skipTarget.id'));

        return ['rule' => $root->rule];
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
}
