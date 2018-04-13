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
        $question->affectRules->load(['effect', 'factors.field'])->each(function ($rule) {
            $isSkip = Rule::answers($this->answers)->compare($rule);
            switch ($rule->effect_type) {
                case SurveyORM\Node::class:
                    $this->fill->node($rule->effect)->reset($isSkip);
                    $rule->effect->childrenNodes->each(function ($node) use ($isSkip) {
                        $this->fill->node($node)->reset($isSkip);
                    });
                    break;

                case SurveyORM\Question::class:
                    if ($rule->effect->node->id === $this->node->id) {
                        $this->affected($rule->effect, $isSkip);
                    } else {
                        $this->fill->node($rule->effect->node)->affected($rule->effect, $isSkip);
                    }

                case SurveyORM\Answer::class:
                    # todo...
                    break;
            }
        });
    }

    public function clean($question)
    {
        $this->set($question, null);
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
