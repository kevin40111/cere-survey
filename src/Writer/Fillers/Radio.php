<?php

namespace Plat\Survey\Writer\Fillers;

class Radio extends Filler
{
    public function set($question, $value)
    {
        $this->contents[$question->id] = $value;

        $this->syncAnswers();

        $this->setRules($question);

        $this->setChildrens($question);

        return $this;
    }

    protected function getEffects($question)
	{
        return $this->node->answers->load(['childrenNodes.questions', 'childrenNodes.answers'])->map(function($answer) use ($question) {
            $pass = $this->contents[$question->id] !== $answer->value;
            return ['target' => $question, 'pass' => $pass];
        });
    }

    protected function isChecked($question)
    {
        return $this->contents[$question->id] !== null && $this->contents[$question->id] !== '-8';
    }

    public function childrens($question)
    {
        return $this->isChecked($question) ? $this->getAnswer($question)->childrenNodes->load(['questions.rule', 'answers.rule', 'rule']) : [];
    }

    private function getAnswer($question)
    {
        return $this->node->answers()->where('value', $this->contents[$question->id])->first();
    }
}
