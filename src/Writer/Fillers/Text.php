<?php

namespace Cere\Survey\Writer\Fillers;

use Cere\Survey\Writer\Rule;

class Text extends Filler
{
    public function set($question, $value)
    {
        $this->contents[$question->id] = $value;

        $this->syncAnswers();

        $this->guard($question);

        $this->setRules($question);

        $this->setChildrens($question);

        return $this;
    }

    protected function getEffects($question)
    {
        return $this->node->answers->load(['childrenNodes.questions', 'childrenNodes.answers'])->map(function($answer) use ($question) {
            $isSkip = is_null($this->contents[$question->id]) ? false : $this->contents[$question->id] !== $answer->value;
            return ['target' => $answer, 'isSkip' => $isSkip];
        });
    }

    protected function isChecked($question)
    {
        return $this->contents[$question->id] !== null && $this->contents[$question->id] !== '-8';
    }

    public function childrens($question)
    {
        return $this->isChecked($question) ? $question->childrenNodes->load(['questions.skiper', 'answers.skiper', 'skiper']) : [];
    }

    private function getAnswer($question)
    {
        return $this->node->answers()->where('value', $this->contents[$question->id])->first();
    }

    public function maxLength($guarder)
    {
        $answer = $this->contents[$guarder->target->id];

        if (! Rule::instance($guarder)->maxLength($answer)) {
            $this->messages = ['字數超過上限'];
        }
    }
}
