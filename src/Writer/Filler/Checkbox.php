<?php

namespace Plat\Survey\Writer\Filler;

use Plat\Eloquent\Survey as SurveyORM;
use Plat\Survey\Writer\Fill;
use Plat\Survey\Writer\Rule;

class Checkbox
{
    public $messages;

    private $node;

    private $answers;

    private $contents = [];

    private $original = [];

    private $fill;

    function __construct($node, $answers)
    {
        $this->node = $node;
        $this->answers = $answers;

        $this->fillOriginal();

        $this->fillContents();

        $this->fill = new Fill($this->answers);
    }

    public function set($question, $value)
    {
        if ($value === '1' && Rule::answers($this->answers)->checkLimit($question)) {
            return ['已達選擇數量上限'];
        }

        $this->contents[$question->id] = $value;

        if ($question->rule()->where('type', 'noneAbove')->exists() && $value === '1') {
            $this->resetChecked($excepts = [$question->id]);
        }

        if (in_array('1', $this->contents)) {
            $this->resetEmpty();
        } else {
            $this->cleanUnChecked();
        }

        $this->syncAnswers();

        $this->setRules($question);

        $this->setChildrens($question);

        return $this;
    }

    public function affected($question, $pass)
    {
        if ($pass) {
            $this->skip($question);
        } else {
            $this->clean($question);
        }
    }

    public function clean($question)
    {
        $this->set($question, null);
    }

    public function skip($question)
    {
        $this->set($question, '-8');
    }

    public function reset($pass)
    {
        $this->node->questions->each(function ($question) use ($pass) {
            $this->affected($question, $pass);
        });
    }

    public function getDirty()
    {
        $dirty = [];

        foreach ($this->contents as $id => $value) {
            if ($value !== $this->original[$id]) {
                $dirty[$id] = $value;
            }
        }

        foreach ($this->fill->getDirty() as $id => $value) {
            $dirty[$id] = $value;
        }

        return $dirty;
    }

    private function setRules($question)
    {
        $rules = Rule::answers($this->answers)->effect($question->id);

        foreach ($rules as $rule) {
            foreach ($rule['questions'] as $question) {
                if ($rule['type'] === 'noneAbove') {
                    $this->contents[$question->id] = '0';
                } else {
                    $this->fill->node($question->node)->affected($question, $rule['pass']);
                }
            }
        }
    }

    private function setChildrens($question)
    {
        $this->getEffects()->load(['childrenNodes.questions', 'childrenNodes.answers'])->each(function ($question) {

            $pass = $this->contents[$question->id] !== '1';

            $question->childrenNodes->each(function ($node) use ($pass) {
                $this->fill->node($node)->reset($pass);
            });
        });
    }

    private function getEffects()
	{
        return $this->node->questions->filter(function ($question) {
            return $this->contents[$question->id] !== $this->original[$question->id];
        });
    }

    private function syncAnswers()
	{
        $this->node->questions->each(function ($question) {
            $this->answers->{$question->id} = $this->contents[$question->id];
        });
    }

    private function fillOriginal()
	{
        $this->node->questions->each(function ($question) {
            $this->original[$question->id] = isset($this->answers->{$question->id}) ? $this->answers->{$question->id} : null;
        });
    }

    private function fillContents()
	{
        $this->contents = $this->original;
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

    private function isChecked($question)
    {
        return $this->contents[$question->id] === '1';
    }

    public function childrens($question)
    {
        return $this->isChecked($question) ? $question->childrenNodes->load(['questions.rule', 'answers.rule', 'rule']) : [];
    }
}
