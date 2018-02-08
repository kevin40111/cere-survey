<?php

namespace Cere\Survey\Extend\Apply;

use Cere\Survey\Eloquent\Extend\Application;

class ApplicationRepository
{
    private $steps = [
        ['view' => 'survey::extend.apply.editor-ng'],
        ['view' => 'survey::extend.apply.bookFinish'],
        ['view' => 'survey::extend.apply.application-ng'],
        ['view' => 'survey::extend.apply.audit'],
    ];

    function __construct($application)
    {
        $this->application = $application;
    }

    public static function create($hook, $book_id)
    {
        $application = $hook->applications()->save(Application::create(['book_id' => $book_id]));

        return new self($application);
    }

    public static function instance($application)
    {
        return new self($application);
    }

    public function getConsent()
    {
        $consent = $this->application->hook->consent;
        return ['consent' => $consent];
    }

    public function setAppliedOptions($fields)
    {
        $this->application->update(['fields' => $fields]);
    }

    public function getAppliedOptions()
    {
        $release = $this->application->hook->options['fields'];

        $appliedFields = $this->application->fields;

        $file = \Files::find($this->application->hook->book->auth['fieldFile_id']);

        $mainListFields = !is_null($file) ? $file->sheets->first()->tables->first()->columns->filter(function ($column) use ($release) {
            return in_array($column->id, $release);
        })->each(function ($column) use ($appliedFields) {
            $column->selected = in_array($column->id, $appliedFields);
        }) : [];

        $mainBookPages = $this->application->hook->book->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) use ($appliedFields, $release){
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
                'mainBook' => $this->application->hook->options['columnsLimit'],
                'mainList' => $this->application->hook->options['fieldsLimit'],
            ],
        ];
    }

    public function getBookFinishQuestions()
    {
        $BookPages = $this->application->hook->book->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) {
            $questions = $page->getQuestions();

            return $carry + [$page->id => $questions];
        }, []);

        return $BookPages;
    }

    public function getStep()
    {
        return $this->steps[$this->application->step];
    }

    public function nextStep()
    {
        $method = $this->steps[$this->application->step]['method'];

        if (method_exists($this, $method)) {
            call_user_func_array([$this, $method], []);
        }

        $this->application->step++;

        $this->application->save();
    }
}
