<?php

namespace Cere\Survey\Extend\Setting;

use Cere\Survey\Eloquent as SurveyORM;
use Cere\Survey\Eloquent\Extend\Hook;
use Auth;

class HookRepository
{
    function __construct($book)
    {
        $this->book = $book;
    }

    public static function book($book)
    {
        return new self($book);
    }

    public function setApplicableOptions($options)
    {
        $extendHook = $this->book->extendHook;
        if (! isset($extendHook)) {
            $this->book->extendHook()->save(new Hook(['options' => $options]));
        } else {
            $this->book->extendHook->update(['options' => $options]);
        }
    }

    public function getApplicableOptions()
    {
        $file = \Files::find($this->book->auth['fieldFile_id']);

        $hook = $this->book->extendHook ?: new Hook;

        $mainListFields = !is_null($file) ? $file->sheets->first()->tables->first()->columns->each(function ($column) use ($hook) {
            $column->selected = in_array($column->id, $hook->options['fields']);
        }) : [];

        $mainBookPages = $this->book->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) use ($hook) {
            $questions = $page->getQuestions();

            foreach ($questions as &$question) {
                $question["selected"] = in_array($question['id'], $hook->options['fields']);
            }

            return $carry + [$page->id => $questions];
        }, []);

        return [
            'fields' => [
                'mainBookPages' => $mainBookPages,
                'mainList' => $mainListFields,
            ],
            'limit' => [
                'mainBook' => $hook->options['columnsLimit'],
                'mainList' => $hook->options['fieldsLimit'],
            ],
        ];
    }

    public function getConsent()
    {
        $extendHook = $this->book->extendHook ?: new Hook;

        return ['consent' => $extendHook->consent];
    }

    public function setConsent($consent)
    {
        $extendHook = $this->book->extendHook;
        if (! isset($extendHook)) {
            $this->book->extendHook()->save(new Hook(['consent' => $consent]));
        } else {
            $this->book->extendHook->update(['consent' => $consent]);
        }
    }
}
