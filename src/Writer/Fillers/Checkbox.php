<?php

namespace Cere\Survey\Writer\Fillers;

use Cere\Survey\Writer\Rule;

class Checkbox extends Filler
{
    protected function preset()
    {
        if (in_array('1', $this->contents)) {
            $this->resetEmpty();
        } else {
            $this->cleanUnChecked();
        }
    }

    protected function setChildrens()
	{
        $this->node->questions->load('childrenNodes')->each(function ($question) {
            if ($this->contents[$question->id] !== $this->original[$question->id]) {
                $this->resetChildrens($question);
            }

            if ($this->isChecked($question)) {
                $this->childrens[$question->id] = $question->childrenNodes->load(['questions.skiper', 'answers.skiper', 'skiper']);
            }
        });
    }

    protected function isChecked($question)
    {
        return is_null($this->contents[$question->id]) ? false : $this->contents[$question->id] === '1';
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
            foreach ($this->contents as $id => &$content) {
                $content = $this->original[$id];
            }
            $this->messages = ['已達選擇數量上限'];
        }
    }

    protected function exclusion($guarder)
    {
        if (Rule::instance($guarder)->compare($this->contents)) {
            $essential = $guarder->operations->first()->factor->target;
            if ($this->contents[$essential->id] !== $this->original[$essential->id]) {
                $this->resetChecked([$essential->id]);
            } else {
                $this->contents[$essential->id] = '0';
            }
        }
    }
}
