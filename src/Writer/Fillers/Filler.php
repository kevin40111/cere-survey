<?php

namespace Cere\Survey\Writer\Fillers;

use Cere\Survey\Eloquent as SurveyORM;
use Cere\Survey\Writer\Fill;
use Cere\Survey\Writer\Rule;

abstract class Filler
{
    public $messages;

    protected $node;

    protected $answers;

    protected $contents = [];

    protected $original = [];

    protected $fill;

    function __construct($node, $answers)
    {
        $this->node = $node;
        $this->answers = $answers;

        $this->fillOriginal();

        $this->fillContents();

        $this->fill = new Fill($this->answers);
    }

    /**
     * Get effected questions from childrens.
     */
    abstract protected function getEffects($question);

    /**
     * Decrement a answer was selected.
     */
    abstract protected function isChecked($question);

    /**
     * Get childrens.
     */
    abstract public function childrens($question);

    protected function setChildrens($question)
    {
        $this->getEffects($question)->each(function ($effect) {
            $effect['target']->childrenNodes->each(function ($node) use ($effect) {
                $this->fill->node($node)->reset($effect['pass']);
            });
        });
    }

    protected function setRules($by)
    {
        foreach ($this->getRules($by) as $rule) {
            foreach ($rule['questions'] as $question) {
                if ($question->node->id === $by->node->id) {
                    $this->affected($question, $rule['pass']);
                } else {
                    $this->fill->node($question->node)->affected($question, $rule['pass']);
                }
            }
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

    public function affected($question, $pass)
    {
        if ($pass) {
            $this->skip($question);
        } else {
            $this->clean($question);
        }
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

    protected function syncAnswers()
	{
        $this->node->questions->each(function ($question) {
            $this->answers[$question->id] = $this->contents[$question->id];
        });
    }

    protected function fillOriginal()
	{
        $this->node->questions->each(function ($question) {
            $this->original[$question->id] = isset($this->answers[$question->id]) ? $this->answers[$question->id] : null;
        });
    }

    protected function fillContents()
	{
        $this->contents = $this->original;
    }

    protected function getRules($question)
	{
        return Rule::answers($this->answers)->effect($question->id);
    }

    private function isSkip($value)
    {
        return $value === '-8';
    }

    public function getSkips()
    {
        $skips = [];

        foreach ($this->contents as $id => $value) {
            $skip = Rule::answers($this->answers)->effect($id);
            $skips = $skips + $skip;
        }

        $skips = $skips + $this->fill->getSkips();

        return $skips;
    }
}
