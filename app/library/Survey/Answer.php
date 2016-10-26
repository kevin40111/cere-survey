<?php

namespace Plat\Survey;

use Plat\Eloquent\Survey as SurveyORM;

class Answer {

    function __construct(SurveyORM\Node $node, SurveyORM\Answer $answer)
    {
        $this->node = $node;
        $this->answer = $answer;
    }

    public static function create(SurveyORM\Node $node, array $attributes)
    {
        $answer = $node->answers()->save(new SurveyORM\Answer([]));

        return new static($node, $answer);
    }

    public static function make(SurveyORM\Answer $answer)
    {
        return new static($answer->node, $answer);
    }

    public static function find($id)
    {
        $answer = SurveyORM\Answer::find($id);

        $node = $answer->node;

        return new static($node, $answer);
    }

    public function update(array $attributes)
    {
        foreach ($attributes as $key => $value)
        {
            $this->answer->$key = $value;
        }

        $this->answer->save();

        return $this;
    }

    public function delete()
    {
        //$this->answer->childrenRule()->delete(); delete childrens

        $deleted = $this->answer->delete();

        return $deleted;
    }

    public function getModel()
    {
        return $this->answer;
    }

    public function getNodeModel()
    {
        return $this->node;
    }

    public function getChildrenNodeModels()
    {
        return $this->answer->childrenNodes;
    }

    public function initNode()
    {

    }

    public function getPaths()
    {
        $class = $this->answer->node->parent->class;

        $paths = $class::make($this->answer->node->parent)->getPaths();

        array_push($paths, $this->answer);

        return $paths;
    }

    public function setChildren($question)
    {
        $childrenRule = $this->answer->childrenRule();

        $rule = $childrenRule->getResults() ?: $childrenRule->create([]);

        $rule->questions()->attach([$question->id]);
    }

}