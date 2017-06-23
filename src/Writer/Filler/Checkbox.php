<?php

namespace Plat\Survey\Writer\Filler;

use Plat\Eloquent\Survey as SurveyORM;
use Plat\Survey\Writer\Fill;
use Plat\Survey\Writer\Rule;

class Checkbox
{
    public $messages;

    public $filled = false;

    public $answers;

    private $contents = [];

    private $original = [];

    private $fill;

    function __construct($question, $answers)
    {
        $this->question = $question;
        $this->answers = $answers;

        $this->fillOriginal();

        $this->fillContents();

        $this->filled = $this->isChecked();
    }

    public function set($value)
    {
        if ($value === '1' && Rule::answers($this->answers)->checkLimit($this->question)) {
            return ['已達選擇數量上限'];
        }

        $this->contents[$this->question->id] = $value;

        if ($this->question->rule()->where('type', 'noneAbove')->exists() && $value === '1') {
            $this->resetChecked($excepts = [$this->question->id]);
        }

        if (in_array('1', $this->contents)) {
            $this->resetEmpty();
        } else {
            $this->cleanUnChecked();
        }

        $this->syncAnswers();

        $this->fill = new Fill($this->answers);

        $rules = Rule::answers($this->answers)->effect($this->question->id);

        foreach ($rules as $rule) {
            foreach ($rule['questions'] as $question) {
                if ($rule['type'] === 'noneAbove') {
                    $this->contents[$question->id] = '0';
                } else {
                    $this->fill->question($question)->affected($rule['pass']);
                }
            }
        }

        $this->setChildrens();

        $this->filled = $this->isChecked();

        return $this;
    }

    public function affected($pass)
    {
        if ($pass) {
            $this->skip();
        } else {
            $this->clean();
        }
    }

    public function clean()
    {
        $this->set(null);
    }

    public function skip()
    {
        $this->set('-8');
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

    private function setChildrens()
    {
        $this->getEffects()->load(['childrenNodes.questions', 'childrenNodes.answers'])->each(function ($question) {
            $question->childrenNodes->each(function ($node) use ($question) {
                $node->questions->each(function ($children) use ($question) {
                    $pass = $this->contents[$question->id] !== '1';
                    $this->fill->question($children)->affected($pass);
                });
            });
        });
    }

    private function getEffects()
	{
        return $this->question->node->questions->filter(function ($question) {
            return $this->contents[$question->id] !== $this->original[$question->id];
        });
    }

    private function syncAnswers()
	{
        $this->question->node->questions->each(function ($question) {
            $this->answers->{$question->id} = $this->contents[$question->id];
        });
    }

    private function fillOriginal()
	{
        $this->question->node->questions->each(function ($question) {
            $this->original[$question->id] = $this->answers->{$question->id};
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

    private function isChecked()
    {
        return $this->contents[$this->question->id] === '1';
    }

    public function childrens()
    {
        return $this->isChecked() ? $this->question->childrenNodes->load(['questions.rule', 'answers.rule', 'rule']) : [];
    }
}
