<?php

namespace Plat\Survey\Writer\Filler;

use Plat\Eloquent\Survey as SurveyORM;
use Plat\Survey\Writer\Fill;
use Plat\Survey\Writer\Rule;

class Radio
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
        $this->contents[$question->id] = $value;

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
                $this->fill->node($question->node)->affected($question, $rule['pass']);
            }
        }
    }

    private function setChildrens($question)
    {
        $this->getEffects()->load(['childrenNodes.questions', 'childrenNodes.answers'])->each(function ($answer) use ($question) {

            $pass = $this->contents[$question->id] !== $answer->value;

            $answer->childrenNodes->each(function ($node) use ($pass) {
                $this->fill->node($node)->reset($pass);
            });
        });
    }

    private function getEffects()
	{
        return $this->node->answers;
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

    private function isChecked($question)
    {
        return $this->contents[$question->id] !== null && $this->contents[$question->id] !== '-8';
    }

    private function getAnswer($question)
    {
        return $this->node->answers()->where('value', $this->contents[$question->id])->first();
    }

    public function childrens($question)
    {
        return $this->isChecked($question) ? $this->getAnswer($question)->childrenNodes->load(['questions.rule', 'answers.rule', 'rule']) : [];
    }
}
