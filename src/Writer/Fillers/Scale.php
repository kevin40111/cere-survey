<?php

namespace Cere\Survey\Writer\Fillers;

class Scale extends Filler
{
    protected function setChildrens() {}

    protected function isChecked($answer)
    {
        $question = $this->node->questions->first();
        return is_null($this->contents[$question->id]) ? false : $this->contents[$question->id] === $answer->value;
    }
}
