<?php

namespace Cere\Survey\Writer\Fillers;

use Cere\Survey\Writer\Rule;

class Text extends Filler
{
    protected function setChildrens()
    {
        $this->node->questions->load('childrenNodes')->each(function ($question) {
            $this->resetChildrens($question);
        });
    }

    protected function isChecked($question)
    {
        return is_null($this->contents[$question->id]) ? false : $this->contents[$question->id] !== '';
    }

    public function maxLength($guarder)
    {
        $answer = $this->contents[$guarder->target->id];

        if (! Rule::instance($guarder)->maxLength($answer)) {
            $this->messages = ['字數超過上限'];
        }
    }
}
