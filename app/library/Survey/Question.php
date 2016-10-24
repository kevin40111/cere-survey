<?php

namespace Plat\Survey;

use Plat\Eloquent\Survey as SurveyORM;

class Question {

    protected $node;

    protected $question;

    function __construct(SurveyORM\Node $node, SurveyORM\Question $question)
    {
        $this->node = $node;
        $this->question = $question;
    }

    public static function create(SurveyORM\Node $node, array $attributes)
    {
        $question = $node->questions()->save(new SurveyORM\Question($attributes));

        return new static($node, $question);
    }

    public static function find($id)
    {
        $question = SurveyORM\Question::find($id);

        $node = $question->node;

        return new static($node, $question);
    }

    public function update(array $attributes)
    {
        foreach ($attributes as $key => $value)
        {
            $this->question->$key = $value;
        }

        $this->question->save();

        return $this;
    }

    public function delete()
    {
        $deleted = $this->question->delete();

        return $deleted;
    }

    public function getModel()
    {
        return $this->question;
    }

    public function getNodeModel()
    {
        return $this->node;
    }

}