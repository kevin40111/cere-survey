<?php

use Cere\Survey\Eloquent as SurveyORM;
use Cere\Survey\Writer\WriterInterface;
use Cere\Survey\Writer\Fill;
use Cere\Survey\Writer\Rule;
use Cere\Survey\Eloquent\Field\Field;
use Plat\Files\Uploader;

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
     * Check if login user in population.
     *
     * @param  int  $book_id
     * @return Response
     */
    public function login($book_id)
    {
        $book = SurveyORM\Book::find($book_id);

        if(!$book->auth || !$book->auth['fieldFile_id']) return Redirect::to('survey/'.$book_id.'/surveyLogin')->withErrors(['fail' => '!問卷尚未設定登入條件']);;

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

        $urls = is_null($page) ? $this->getNextUrl($book_id) : [];

        return ['page' => $page, 'urls' => $urls];
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

        $urls = is_null($nextPage) ? $this->getNextUrl($book_id) : [];

        return ['page' => $nextPage, 'urls' => $urls];
    }

    private function getNextUrl($book_id)
    {
        $information = $this->writer->user()->information();

        return SurveyORM\Book::find($book_id)->extendHook->applications->filter(function ($application) use ($information) {
            return $application->status == 1 && Rule::instance($application)->compare($information);
        })->map(function ($application) {
            $this->writer->user()->sign($application->book);
            return '/survey'.'/'. $application->book->id .'/page';
        });
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

        $nodes = $page->childrenNodes->load(['skiper', 'questions.skiper', 'answers.skiper', 'images']);

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

        if (count($filler->messages) > 0) {
            return ['dirty' => $filler->getOriginal(), 'messages' => $filler->messages];
        }

        $dirty = $filler->getDirty();

        $this->writer->update($dirty);

        $skips = $filler->getSkips();

        return ['dirty' => $dirty, 'logs' => DB::connection('survey')->getQueryLog(), 'skips' => $skips];
    }

    private function getSkips($items, $fields, $relations)
    {
        return $items->reduce(function ($skips, $item) use ($fields, $relations) {
            if ($item->skiper) {
                $skips = array_add($skips, $item->skiper->id, Rule::instance($item->skiper)->compare($fields));
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

    private function getAnswers($page, $fields)
    {
        $answers = $page->getQuestions()->load('field')->reduce(function($answers, $question) use ($fields) {
            $answers[$question->id] = $fields[$question->field->id];
            return $answers;
        }, []);

        return $answers;
    }

    public function getUpload($book_id, $serial)
    {
        $value = Crypt::decrypt($serial);
        return Uploader::getFile($value);
    }
}