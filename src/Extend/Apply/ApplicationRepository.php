<?php

namespace Cere\Survey\Extend\Apply;

use Cere\Survey\Eloquent as SurveyORM;
use Auth;
use Input;
use DB;
use Cere\Survey\Eloquent\Field\Field;
use Cere\Survey\RuleRepository;

class ApplicationRepository
{
    private $steps = [
        ['view' => 'survey::extend.apply.contract', 'method' => 'stepContract'],
        ['view' => 'survey::extend.apply.editor-ng'],
        ['view' => 'survey::extend.apply.bookFinish'],
        ['view' => 'survey::extend.apply.application-ng'],
        ['view' => 'survey::extend.apply.audit'],
    ];

    function __construct($book)
    {
        $this->book = $book;
        $this->member = Auth::user()->members()->orderBy('logined_at', 'desc')->first();
    }

    public static function book($book)
    {
        return new self($book);
    }

    public static function application($application)
    {
        $instance = new self($application->book);

        $instance->application = $application;

        return $instance;
    }

    public function getConsent()
    {
        $consent = $this->book->extendHook->consent;
        return ['consent' => $consent];
    }
    public function setAppliedOptions($fields)
    {
        $application = $this->book->extendHook->applications()->where('member_id', $this->member->id)->first();

        $application->update(['fields' => $fields]);
    }

    public function getAppliedOptions()
    {
        $application = $this->book->extendHook->applications()->where('member_id', $this->member->id)->first();

        $release = $this->book->extendHook->options['fields'];

        $appliedFields = $application->fields;

        $file = \Files::find($this->book->auth['fieldFile_id']);

        $mainListFields = !is_null($file) ? $file->sheets->first()->tables->first()->columns->filter(function ($column) use ($release) {
            return in_array($column->id, $release);
        })->each(function ($column) use ($appliedFields) {
            $column->selected = in_array($column->id, $appliedFields);
        }) : [];

        $mainBookPages = $this->book->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) use ($appliedFields, $release){
            $questions = $page->getQuestions();

            $questions = array_filter($questions, function($question) use ($release){
                return in_array($question['id'], $release);
            });

            foreach ($questions as &$question) {
                $question["selected"] = in_array($question['id'], $appliedFields);
            }

            return $carry + [$page->id => array_values($questions)];
        }, []);

        return [
            'fields' => [
                'mainBookPages' => $mainBookPages,
                'mainList' => $mainListFields,
            ],
            'limit' => [
                'mainBook' => $this->book->extendHook->options['columnsLimit'],
                'mainList' => $this->book->extendHook->options['fieldsLimit'],
            ],
        ];
    }

    public function getApplication()
    {
        return $this->book->extendHook->applications()->where('member_id', $this->member->id)->first();
    }

    public function resetApplication()
    {
        $application = $this->book->applications()->OfMe()->withTrashed()->first();
        $application->reject = false;
        $application->save();
        $extBook = SurveyORM\Book::find($application->ext_book_id);
        RuleRepository::target($extBook)->deleteRule();
        $this->book->applications()->OfMe()->delete();
        return $this->getAppliedOptions();
    }

    private function createExtBook()
    {
        $newDoc = ['title' => $this->book->title .'(加掛題本)', 'type' => 6];

        Input::replace(['fileInfo' => $newDoc]);

        $user = Auth::user();

        $doc = \ShareFile::whereNull('folder_id')->first();

        $folderComponent = new \Plat\Files\FolderComponent($doc->is_file, $user);

        $folderComponent->setDoc($doc);

        return $folderComponent->createComponent()['doc'];
    }

    public function getBookFinishQuestions()
    {
        $book = $this->book->extendHook->applications()->where('member_id', $this->member->id)->first()->book;

        $BookPages = $book->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) {
            $questions = $page->getQuestions();

            return $carry + [$page->id => $questions];
        }, []);

        return $BookPages;
    }

    public function getStep()
    {
        $application = $this->book->extendHook->applications()->where('member_id', $this->member->id)->first();

        return $this->steps[$application->step];
    }

    public function nextStep()
    {
        $application = $this->book->extendHook->applications()->where('member_id', $this->member->id)->first();

        if (method_exists($this, $this->steps[$application->step]['method'])) {
            call_user_func_array([$this, $this->steps[$application->step]['method']], []);
        }

        $application->step++;

        $application->save();
    }

    public function stepContract()
    {
        $application = $this->book->extendHook->applications()->where('member_id', $this->member->id)->first();
    }
}
