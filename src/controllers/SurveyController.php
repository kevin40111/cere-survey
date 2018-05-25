<?php

use Cere\Survey\Eloquent as SurveyORM;
use Cere\Survey\Writer\WriterInterface;
use Cere\Survey\Writer\Fillers\Filler;
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
        $answers = Input::get('answers');
        $page = SurveyORM\Node::find(Input::get('page.id'));

        if (count($missings = $this->checkPage($page, $answers)) > 0) {
            return ['missings' => $missings];
        }

        $this->writer->update($answers);

        $nextPage = $page->next() ? $this->checkAndJump($page->next(), $answers) : NULL;

        $this->writer->setPage(isset($nextPage->id) ? $nextPage->id : NULL);

        $urls = is_null($nextPage) && SurveyORM\Book::find($book_id)->extendHook()->exists() ? $this->getNextUrl($book_id) : [];

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
            return ! isset($answers[$question->id]);
        })->values();

        return $missings;
    }

    private function checkAndJump($page, $answers)
    {
        $questions = $page->getQuestions();

        $skips = 0;
        foreach ($questions as $question) {
            if (isset($answers[$question->id]) && $answers[$question->id] == -8) {
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

        $fields = $this->writer->all();
        $nodes = $page->childrenNodes->load(['skiper', 'questions.skiper', 'answers.skiper', 'images'])->filter(function ($node) use ($fields) {
            return ! $node->skiper || ! Rule::instance($node->skiper)->compare($fields);
        });

        return ['nodes' => $nodes, 'logs' => DB::connection('survey')->getQueryLog()];
    }

    public function sync()
    {
        $node = SurveyORM\Node::find(Input::get('node.id'));

        $filler = Filler::instance($node, Input::get('answers'));

        $filler->set(Input::get('contents'));

        if (count($filler->messages) > 0) {
            return ['contents' => $filler->contents, 'messages' => $filler->messages, 'childrens' => $filler->getChildrens()];
        }

        $dirty = $filler->getDirty();

        $skipers = $filler->getSkipers();

        return ['contents' => $filler->contents, 'dirty' => $dirty, 'skipers' => $skipers, 'childrens' => $filler->getChildrens(), 'logs' => DB::connection('survey')->getQueryLog()];
    }

    public function getUpload($book_id, $serial)
    {
        $value = Crypt::decrypt($serial);
        return Uploader::getFile($value);
    }
}
