<?php

namespace Plat\Survey;

use Plat\Eloquent\Survey as SurveyORM;

class Node {    

    public function rules()
    {
        $rule = $this->node->childrenRule;

        return $rule ? $rule->node : \Illuminate\Database\Eloquent\Collection::make([]);
    }

    public function getParent()
    {
        if ($this->node->byRules->isEmpty()) {
            return Book::make($this->book);
        }

        $children = $this->node->byRules->first(function($index, $rule) {
            return $rule->is->expression == 'children';
        });

        $parameter = $children->is->parameters->first();

        switch ($parameter->type) {
            case 'answer':
                $parent = Answer::find($parameter->answer);
                break;

            case 'question':
                $parent = Question::find($parameter->question);
                break;
        }

        return $parent;
    }

    public function setChildren($node)
    {
        $childrenRule = $this->node->childrenRule();

        $rule = $childrenRule->getResults() ?: $childrenRule->create([]);

        $rule->node()->attach([$node->id]);
    }

}