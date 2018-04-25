<?php

use Cere\Survey\Eloquent as SurveyORM;
use Cere\Survey\Writer\WriterInterface;
use Cere\Survey\Writer\Fill;
use Cere\Survey\Writer\Rule;
use Cere\Survey\Eloquent\Field\Field;

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
        $this->writer = $writer;
    }

    /**
     * Display a page of the survey.
     *
     * @return Response
     */
    public function page($book_id)
    {
        if (! $this->writer->exist()) {
            $firstPage = SurveyORM\Book::find($book_id)->childrenNodes->first();
            $this->writer->increment(['page_id' => $firstPage->id]);
        }

        return View::make('survey::layout-survey')->nest('context', 'survey::demo-ng');
    }

    /**
     * Display a page of the survey login.
     *
     * @return Response
     */
    public function surveyLogin($book_id)
    {
        $this->writer->user()->logout();
        $book = SurveyORM\Book::find($book_id);
        $fields = Field::find($book->auth['inputFields']);

        $view = Carbon\Carbon::now()->between($book->auth['start_at'], $book->auth['close_at']) ? 'surveylogin-ng' : 'surveydisabled-ng';

        return View::make('survey::layout-survey')->nest('context', 'survey::auth.' . $view, ['book' => $book, 'fields' => $fields]);
    }

    /**
     * Display a page of the demo login.
     *
     * @return Response
     */
    public function demoLogin()
    {
        return View::make('survey::layout-survey')->nest('context', 'survey::auth.demologin-ng');
    }

    /**
     * Check if login user in population.
     *
     * @param  int  $book_id
     * @return Response
     */
    public function login($book_id)
    {
        $this->writer->user()->login(Input::all());

        if ($this->writer->user()->logined()) {
            return Redirect::to('survey/'.$book_id.'/page');
        } else {
            return Redirect::to('survey/'.$book_id.'/surveyLogin')->withErrors(['fail' => '! 登入資料不在名單內']);
        }
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

    public function getPage($book_id)
    {
        $fields = $this->writer->all();

        $page = SurveyORM\Node::find($fields['page_id']);

        return ['page' => $page];
    }

    /**
     * Next page in book.
     *
     * @param  int  $book_id
     * @return Response
     */
    public function nextPage($book_id)
    {
        $answers = $this->writer->all();
        $page = SurveyORM\Node::find(Input::get('page.id'));

        if (count($missings = $this->checkPage($page, $answers)) > 0) {
            return ['missings' => $missings];
        }

        $nextPage = $page->next() ? $this->checkAndJump($page->next(), $answers) : NULL;

        $this->writer->setPage(isset($nextPage->id) ? $nextPage->id : NULL);

        $url = is_null($nextPage) ? $this->getNextUrl($book_id) : NULL;

        return ['page' => $nextPage, 'url' => $url];
    }

    private function getNextUrl($book_id)
    {
        $extBooks = $this->getExtBook($book_id);
        $extended = (count($extBooks) == 0) ? false : true;
        if ($extended) {
            /**
             * todo
             */
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

                return '/survey'.'/'.$extBook_id.'/page';
            }
            if ($this->type == 'demo') {
                return '/surveyDemo'.'/'.$book_id.'/demoLogin';
            }
        }
    }

    private function checkPage($page, $answers)
    {
        $missings = $page->getQuestions()->filter(function ($question) use ($answers) {
            return ! isset($answers[$question->field->id]);
        })->values();

        return $missings;
    }

    private function checkAndJump($page, $answers)
    {
        $questions = $page->getQuestions();

        $skips = 0;
        foreach ($questions as $question) {
            if (isset($answers[$question->field->id]) && $answers[$question->field->id] == -8) {
                $skips++;
            }
        }

        return $skips == sizeof($questions) && sizeof($questions) != 0
            ? $page->next() ? $this->checkAndJump($page->next(), $answers) : NULL
            : $page;
    }

    /**
     * Show nodes in a page node.
     *
     * @return Response
     */
    public function getNodes()
    {
        $page = SurveyORM\Node::find(Input::get('page.id'));;

        $nodes = $page->childrenNodes->load(['rule', 'questions.rule', 'answers.rule']);

        $fields = $this->writer->all();

        $skips = $this->getSkips($nodes, $fields, ['questions' => [], 'answers' => []]);

        $answers = $this->getAnswers($page, $fields);

        return ['nodes' => $nodes, 'answers' => $answers, 'skips' => $skips, 'logs' => DB::connection('survey')->getQueryLog()];
    }

    /**
     * Save answer.
     *
     * @return Response
     */
    public function saveAnswer($book_id)
    {
        $question = SurveyORM\Question::find(Input::get('question.id'));
        $answers = $this->writer->all();
        $filler = Fill::answers($answers)->node($question->node);
        $filler->set($question, Input::get('value'));
        $dirty = $filler->getDirty();

        $this->writer->update($dirty);

        $fields = $this->writer->all();

        $skips = $filler->getSkips();

        return ['dirty' => $dirty, 'message' => $filler->messages, 'logs' => DB::connection('survey')->getQueryLog(), 'skips' => $skips];
    }

    private function getSkips($items, $fields, $relations)
    {
        return $items->reduce(function ($skips, $item) use ($fields, $relations) {
            if ($item->rule) {
                $skips = array_add($skips, $item->rule->id, Rule::answers($fields)->compare($item->rule));
            }
            foreach ($relations as $relation => $nests) {
                $skips += $this->getSkips($item->$relation, $fields, $nests);
            }
            return $skips;
        }, []);
    }

    public function getChildrens()
    {
        $question = SurveyORM\Question::find(Input::get('question.id'));

        $answers = $this->writer->all();

        $filler = Fill::answers($answers)->node($question->node);

        return ['nodes' => $filler->childrens($question)];
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

    private function getAnswers($page, $fields)
    {
        $answers = $page->getQuestions()->load('field')->reduce(function($answers, $question) use ($fields) {
            $answers[$question->id] = $fields[$question->field->id];
            return $answers;
        }, []);

        return $answers;
    }
}