<?php

namespace Cere\Survey\Writer\Fillers;

use Cere\Survey\Writer\Rule;

class Checkbox extends Filler
{
    public function set($question, $value)
    {
        $this->contents[$question->id] = $value;

        if (in_array('1', $this->contents)) {
            $this->resetEmpty();
        } else {
            $this->cleanUnChecked();
        }

        $this->syncAnswers();

        $this->guard($question);

        $this->setRules($question);

        $this->setChildrens($question);

        return $this;
    }

    protected function getEffects($question)
	{
        return $this->node->questions->filter(function ($question) {
            return $this->contents[$question->id] !== $this->original[$question->id];
        })->load(['childrenNodes.questions', 'childrenNodes.answers'])->map(function ($question) {
            $isSkip = is_null($this->contents[$question->id]) ? false : $this->contents[$question->id] !== '1';
            return ['target' => $question, 'isSkip' => $isSkip];
        });
    }

    protected function isChecked($question)
    {
        return $this->contents[$question->id] === '1';
    }

    public function childrens($question)
    {
        return $this->isChecked($question) ? $question->childrenNodes->load(['questions.skiper', 'answers.skiper', 'skiper']) : [];
    }

    private function resetChecked($excepts)
    {
        foreach ($this->contents as $question_id => &$content) {
            if (! in_array($question_id, $excepts) && $content === '1') {
                $content = '0';
            }
        }
    }

    private function resetEmpty()
	{
        foreach ($this->contents as &$content) {
            if (is_null($content)) {
                $content = '0';
            }
        }
    }

    private function cleanUnChecked()
	{
        foreach ($this->contents as &$content) {
            if (empty($content)) {
                $content = null;
            }
        }
    }

    private function isSkip($value)
    {
        return $value === '-8';
    }

    protected function lessThan($guarder)
    {
        $amount = $this->node->questions->reduce(function ($amount, $question) {
            return $amount += $this->contents[$question->id] === '1' ? 1 : 0;
        }, 0);

        if (! Rule::instance($guarder)->lessThan($amount)) {
            $this->messages = ['已達選擇數量上限'];
        }
    }

    protected function exclusion($guarder, $question)
    {
        $this->syncAnswers();
        if (Rule::instance($guarder)->compare($this->answers)) {
            if ($guarder->operations->first()->factor->target->id === $question->id) {
                $this->resetChecked([$question->id]);
            } else {
                $this->contents[$guarder->operations->first()->factor->target->id] = '0';
            }
        }
    }
}
