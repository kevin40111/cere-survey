<?php

namespace Cere\Survey\Writer\Fillers;

class Radio extends Filler
{
    protected function setChildrens()
    {
        $this->node->answers->load('childrenNodes')->map(function($answer) {
            $this->resetChildrens($answer);

            if ($this->isChecked($answer)) {
                $this->childrens[$answer->id] = $answer->childrenNodes->load(['questions.skiper', 'answers.skiper', 'skiper']);
            }
        });
    }

    protected function isChecked($answer)
    {
        $question = $this->node->questions->first();
        return is_null($this->contents[$question->id]) ? false : $this->contents[$question->id] === $answer->value;
    }
}
