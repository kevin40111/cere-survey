<?php

namespace Plat\Survey\Writer\Filler;

use Plat\Eloquent\Survey as SurveyORM;
use Plat\Survey\Writer\Fill;
use Plat\Survey\Writer\Rule;

class Radio
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

        $this->setAnswer();

        $this->filled = $this->isChecked();
    }

    public function set($value)
    {
        $this->contents[$this->question->id] = $value;

        $this->syncAnswers();

        $this->fill = new Fill($this->answers);

        $rules = Rule::answers($this->answers)->effect($this->question->id);

        foreach ($rules as $rule) {
            foreach ($rule['questions'] as $question) {
                $this->fill->question($question)->affected($rule['pass']);
            }
        }

        $this->setChildrens();

        $this->setAnswer();

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
        $this->getEffects()->load(['childrenNodes.questions', 'childrenNodes.answers'])->each(function ($answer) {
            $answer->childrenNodes->each(function ($node) use ($answer) {
                $node->questions->each(function ($children) use ($answer) {
                    $pass = $this->contents[$this->question->id] !== $answer->value;
                    $this->fill->question($children)->affected($pass);
                });
            });
        });
    }

    private function getEffects()
	{
        return $this->question->node->answers;
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

    private function isChecked()
    {
        return $this->contents[$this->question->id] !== null && $this->contents[$this->question->id] !== '-8';
    }

    private function setAnswer()
    {
        if ($this->isChecked()) {
            $this->answer = $this->question->node->answers()->where('value', $this->contents[$this->question->id])->first();
        }
    }

    public function childrens()
    {
        return $this->answer ? $this->answer->childrenNodes->load(['questions.rule', 'answers.rule', 'rule']) : [];
    }
}
