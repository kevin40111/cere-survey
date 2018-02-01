<?php

namespace Cere\Survey\Extend\Setting;

use Cere\Survey\Eloquent as SurveyORM;
use Cere\Survey\Eloquent\Extend\Hook;
use Auth;

class SettingRepository
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

        $extendHook = $this->book->extendHook ?: new Hook;

        $optionColumns = !is_null($file) ? $file->sheets->first()->tables->first()->columns->each(function ($column) use ($extendHook) {
            $column->selected = in_array($column->id, $extendHook->options['fields']);
        }) : [];

        $pages = $this->book->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) use ($extendHook) {
            $questions = $page->getQuestions();

            foreach ($questions as &$question) {
                $question["selected"] = in_array($question['id'], $extendHook->options['fields']);
            }

            return $carry + [$page->id => $questions];
        }, []);

        return [
            'options' => $extendHook->options,
            'options' => [
                'columns' => $optionColumns,
                'pages' => $pages,
            ],
        ];
    }
}
