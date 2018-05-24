<?php

namespace Cere\Survey\Writer\Fillers;

use Cere\Survey\Eloquent as SurveyORM;

class Gear extends Filler
{
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

    public function questions()
    {
        return $this->node->questions->each(function ($question) {
            $click_answer = $this->getAnswer($question);

            $question->childrens = $this->isChecked($question) ? $question->childrenNodes()->with(['answers' => function ($query) use ($click_answer) {
                $query->where('category_id', $click_answer->id);
            }, 'questions'])->get() : [];
        });
    }

    private function getAnswer($question)
    {
         return $this->node->answers()->where('value', $this->contents[$question->id])->first();
    }
}
