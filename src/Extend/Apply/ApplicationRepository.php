<?php

namespace Cere\Survey\Extend\Apply;

use Cere\Survey\Eloquent\Extend\Application;
use Cere\Survey\Field\FieldComponent;

class ApplicationRepository
{
    private $steps = [
        ['view' => 'survey::extend.apply.editor', 'next_method' => 'checkBookHasQuestion', 'pre_method' => 'noCheck'],
        ['view' => 'survey::extend.apply.book_finish', 'next_method' => 'setBookFinish', 'pre_method' => 'noCheck'],
        ['view' => 'survey::extend.apply.fields', 'next_method' => 'checkAppliedFields', 'pre_method' => 'setBookEdit'],
        ['view' => 'survey::extend.apply.audit', 'next_method' => 'noCheck', 'pre_method' => 'noCheck'],
    ];

    function __construct($application)
    {
        $this->application = $application;
    }

    public static function create($hook, $book, $member)
    {
        $fieldComponent = FieldComponent::createComponent(['title' => $book->title], $member->user);

        $book->sheet()->associate($fieldComponent->file->sheets()->first());

        $book->save();

        $application = $hook->applications()->save(Application::create(['book_id' => $book->id, 'member_id' => $member->id]));

        return new self($application);
    }

    public static function instance($application)
    {
        return new self($application);
    }

    public function getConsent()
    {
        $hook = $this->application->hook;
        return ['consent' => $hook->consent, 'due' => $hook->due];
    }

    public function setAppliedOptions($fields)
    {
        $this->application->update(['fields' => $fields]);
    }

    public function getAppliedOptions()
    {
        $appliedFields = $this->application->fields;

        $file = \Files::find($this->application->hook->book->auth['fieldFile_id']);

        $mainListFields = !is_null($file) ? $file->sheets->first()->tables->first()->columns->filter(function ($column) {
            return in_array($column->id, $this->application->hook->main_list_limit['fields']);
        })->values()->each(function ($column) use ($appliedFields) {
            $column->selected = in_array($column->id, $appliedFields);
        }) : [];

        $mainBookPages = $this->application->hook->book->childrenNodes->reduce(function ($carry, $page) use ($appliedFields){
            $questions = $page->getQuestions();

            $questions = array_filter($questions, function($question) {
                return in_array($question['id'], $this->application->hook->main_book_limit['fields']);
            });

            foreach ($questions as &$question) {
                $question["selected"] = in_array($question['id'], $appliedFields);
            }

            if (! empty($questions)) {
                array_push($carry, ['fields' => $questions]);
            }

            return $carry;
        }, []);

        return [
            'mainListLimit' => [
                'fields' => $mainListFields,
                'amount' => $this->application->hook->main_list_limit['amount'],
            ],
            'mainBookLimit' => [
                'pages' => $mainBookPages,
                'amount' => $this->application->hook->main_book_limit['amount'],
            ],
            'status' => $this->application->status,
        ];
    }

    public function getBookFinishQuestions()
    {
        $BookPages = $this->application->book->childrenNodes->reduce(function ($carry, $page) {
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
        $method = $this->steps[$this->application->step]['next_method'];

        $errors = method_exists($this, $method) ? call_user_func_array([$this, $method], []) : [];

        if (empty($errors)) {
            $this->application->step++;
            $this->application->save();
        }

        return $errors;
    }

    public function preStep()
    {
        $method = $this->steps[$this->application->step]['pre_method'];

        $errors = method_exists($this, $method) ? call_user_func_array([$this, $method], []) : [];

        if (empty($errors)) {
            $this->application->step--;
            $this->application->save();
        }

        return $errors;
    }

    private function noCheck()
    {
        return [];
    }

    private function checkBookHasQuestion()
    {
        $questions = $this->application->book->childrenNodes->reduce(function ($carry, $page) {
            $questions = $page->getQuestions();
            return array_merge($carry, $questions);
        }, []);

        return sizeof($questions) > 0 ? [] : [['description' => '您沒有新增題目']];
    }

    private function setBookEdit()
    {
        $book = $this->application->hook->book;

        $book->lock = false;

        $book->save();

        return [];
    }

    private function setBookFinish()
    {
        $book = $this->application->hook->book;

        $book->lock = true;

        $book->save();

        return [];
    }

    private function checkAppliedFields()
    {
        return [];
    }

    public function backToEdit()
    {
        $application = $this->application;

        $application->step = 0;

        $application->status = 0;

        $book = $application->book;

        $book->lock = false;

        $application->push();
    }

    public function backToApply()
    {
        $application = $this->application;

        $application->step = 2;

        $application->status = 0;

        $application->save();
    }
}
