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
        $rule = $this->answer->childrenRule;

        return $rule ? $rule->questions : \Illuminate\Database\Eloquent\Collection::make([]);
    }

    public function getPath()
    {
         
        return $this->answer->node->byRules->isEmpty() ? $this->answer : $this->answer->node->byRules;
    }

    public function setChildren($question)
    {
        $childrenRule = $this->answer->childrenRule();

        $rule = $childrenRule->getResults() ?: $childrenRule->create([]);

        $rule->questions()->attach([$question->id]);
    }

}