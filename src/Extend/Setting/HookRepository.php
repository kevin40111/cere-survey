<?php

namespace Cere\Survey\Extend\Setting;

use Cere\Survey\Eloquent\Extend\Hook;
use Files;

class HookRepository
{
    function __construct($hook)
    {
        $this->hook = $hook;
    }

    public static function instance($hook)
    {
        return new self($hook);
    }

    public static function create($book, $file)
    {
        $book->extendHook()->save(new Hook(['title' => $book->title, 'file_id' => $file->id]));
    }

    public function setApplicableOptions($name, $options)
    {
        $this->hook->update([$name => $options]);
    }

    public function getApplicableOptions()
    {
        $file = \Files::find($this->hook->book->auth['fieldFile_id']);

        $mainListFields = !is_null($file) ? $file->sheets->first()->tables->first()->columns->each(function ($column) {
            $column->selected = in_array($column->id, $this->hook->main_list_limit['fields']);
        }) : [];

        $mainBookPages = $this->hook->book->childrenNodes->reduce(function ($carry, $page) {
            $questions = $page->getQuestions();

            foreach ($questions as &$question) {
                $question["selected"] = in_array($question['id'], $this->hook->main_book_limit['fields']);
            }

            array_push($carry, ['fields' => $questions]);

            return $carry;
        }, []);

        return [
            'mainListLimit' => [
                'fields' => $mainListFields,
                'amount' => $this->hook->main_list_limit['amount'],
            ],
            'mainBookLimit' => [
                'pages' => $mainBookPages,
                'amount' => $this->hook->main_book_limit['amount'],
            ],
        ];
    }

    public function getConsent()
    {
        return [
            'consent' => $this->hook->consent,
            'due' => $this->hook->due
        ];
    }

    public function setConsent($consent)
    {
        $this->hook->update(['consent' => $consent['content'], 'due' => $consent['due']]);
    }
}
