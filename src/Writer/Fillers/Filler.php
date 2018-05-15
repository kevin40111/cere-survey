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

    public $skips = [];

    function __construct($node, $answers)
    {
        $this->node = $node->load('questions.field');
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

    public function effected()
    {
        $this->node->skipers->each(function ($skiper) {
            $this->skips[$skiper->id] = Rule::instance($skiper)->compare($this->answers);
            $this->reset($this->skips[$skiper->id]);
        });
    }

    protected function setChildrens($question)
    {
        $this->getEffects($question)->each(function ($effect) {
            $effect['target']->childrenNodes->each(function ($node) use ($effect) {
                $this->fill->node($node)->reset($effect['isSkip']);
            });
        });
    }

    protected function setRules($question)
    {
        $question->effects->each(function ($operation) {
            $this->setEffected($operation);
        });
    }

    private function setEffected($effected)
    {
        if ($effected instanceof SurveyORM\Rule\Skiper) {
            if (array_key_exists($effected->id, $this->getSkips())) {
                return;
            }

            $this->fill->sync($this->answers)->node($effected->node)->effected();
        } else if ($effected instanceof SurveyORM\Rule\Operation) {
            if ($effected->effect()->exists()) {
                $this->setEffected($effected->effect);
            }
        }
    }

    protected function guard($question)
    {
        $this->node->guarders->sortBy('priority')->each(function ($guarder) use ($question) {
            call_user_func([$this, $guarder->method], $guarder, $question);
        });
    }

    public function clean($question)
    {
        if ($this->contents[$question->id] == '-8') {
            $this->set($question, null);
        }
    }

    public function skip($question)
    {
        $this->set($question, '-8');
    }

    public function reset($isSkip)
    {
        $this->node->questions->each(function ($question) use ($isSkip) {
            $this->affected($question, $isSkip);
        });
    }

    public function affected($question, $isSkip)
    {
        if ($isSkip) {
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

    public function getSkips()
    {
        $this->skips += $this->fill->getSkips();

        return $this->skips;
    }

    public function getOriginal()
    {
        return $this->original;
    }

    protected function syncAnswers()
	{
        $this->node->questions->each(function ($question) {
            $this->answers[$question->field->id] = $this->contents[$question->id];
        });
    }

    protected function fillOriginal()
	{
        $this->node->questions->each(function ($question) {
            $this->original[$question->id] = isset($this->answers[$question->field->id]) ? $this->answers[$question->field->id] : null;
        });
    }

    protected function fillContents()
	{
        $this->contents = $this->original;
    }

    private function isSkip($value)
    {
        return $value === '-8';
    }
}
