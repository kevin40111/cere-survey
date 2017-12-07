<?php

use Cere\Survey\Eloquent as SurveyORM;
use Cere\Survey\SurveySession;
use Cere\Survey\Writer\WriterInterface;
use Cere\Survey;
use Cere\Survey\Writer\Fill;
use Cere\Survey\Field\FieldRepository;

class SurveyController extends \BaseController {
    /**
     * init the repository of the survey.
     *
     * @param  int  $book_id
     * @param  string  $type
     * @return Response
     */
    function __construct(WriterInterface $writer)
    {
        $this->user_id = $writer->getId();
        $this->type = $writer->getType();
        $this->writer = $writer;
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
        $file_book = SurveyORM\Book::find($this->writer->book_id);
        $now = Carbon\Carbon::now();
        if ((!is_null($file_book->start_at) && $now < $file_book->start_at) || (!is_null($file_book->close_at) && $now > $file_book->close_at)) {
            return View::make('survey::layout-survey')->nest('context', 'survey::surveydisabled-ng', ['file_book' => $file_book]);
        }
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
    public function login($book_id)
    {
        SurveySession::logout();

        $login_id = Input::get('id');

        $file_book = SurveyORM\Book::find($book_id);

        $table = Files::find($file_book->rowsFile_id)->sheets->first()->tables->first();

        $in_rows  = FieldRepository::target($table, $file_book->file->created_by)->rowExists(['C'.$file_book->loginRow_id => $login_id]);

        if (!$in_rows && !$file_book->no_population) {
            return Redirect::to('survey/'.$book_id.'/surveyLogin')->withErrors(['fail' => '! 登入資料不在名單內']);
        }

        SurveySession::login($book_id, $login_id);

        if (!$this->writer->exist()) {
            $this->writer->increment();
        }

        return Redirect::to('survey/'.$book_id.'/page');
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
        $answers = $this->writer->all();
        $firstPage = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->first();
        $page = $answers->page_id ? SurveyORM\Node::find($answers->page_id)->next : $firstPage;

        if (Input::get('next') && count($missings = $this->checkPage($page, $answers)) == 0) {
            $nextPage = $page->next ? $this->checkAndJump($page->next, $answers) : NULL;
            $complete = $nextPage ? $nextPage->previous : $page;
            $this->writer->setPage($this->user_id, $complete->id);
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
            return ! isset($answers->{$question['name']});
        });

        return array_values($missings);
    }

    private function checkAndJump($page, $answers)
    {
        $questions = $page->getQuestions();

        $skips = 0;
        foreach ($questions as $question) {
            if (isset($answers->{$question['name']}) && $answers->{$question['name']} == -8) {
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

        return ['nodes' => $nodes];
    }

    /**
     * Show children nodes.
     *
     * @return Response
     */
    public function getChildren($book_id)
    {
        $question = SurveyORM\Field\Field::find(Input::get('question.id'));
        $answers = $this->writer->all();
        $filler = Fill::answers($answers)->node($question->node);

        if (Input::has('value')) {
            $filler->set($question, Input::get('value'));
            if (!empty($filler->messages)) {
                return ;
            }

            foreach ($filler->getDirty() as $id => $value) {
                $this->writer->put($id, $value);
            }
        }

        return ['nodes' => $filler->childrens($question), 'answers' => $this->writer->all(), 'message' => $filler->messages, 'logs' => DB::getQueryLog()];
    }

    /**
     * Clean demo answers .
     *
     * @param  int  $book_id
     * @return Response
     */
    public function cleanAnswers($book_id)
    {
        $this->writer->decrement();

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