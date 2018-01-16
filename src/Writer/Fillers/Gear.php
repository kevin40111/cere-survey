<?php

namespace Cere\Survey\Writer\Fillers;

use Cere\Survey\Eloquent as SurveyORM;

class Gear extends Filler
{
    public function set($question, $value)
    {
        $this->contents[$question->id] = $value;

        $this->syncAnswers();

        $this->setRules($question);

        $this->setChildrens($question);

        return $this;
    }

    public function setAnswer($answer_id)
    {
        $this->answer = SurveyORM\Answer::find($answer_id);
    }

    protected function getEffects($question)
    {
        return $this->node->answers->load(['childrenNodes.questions', 'childrenNodes.answers'])->map(function($answer) use ($question) {
            $isSkip = is_null($this->contents[$question->id]) ? false : $this->contents[$question->id] !== $answer->value;
            return ['target' => $question, 'isSkip' => $isSkip];
        });
    }

    protected function isChecked($question)
    {
        return $this->contents[$question->id] !== null && $this->contents[$question->id] !== '-8';
    }

    public function childrens($question)
    {
        $click_answer = $this->getAnswer($question);

        return $this->isChecked($question) ? $question->childrenNodes()->with(['answers' => function ($query) use ($click_answer) {
            $query->where('belong', $click_answer->id);
        }, 'questions'])->get() : [];
    }

    private function getAnswer($question)
    {
         return $this->node->answers()->where('value', $this->contents[$question->id])->first();
    }
}
