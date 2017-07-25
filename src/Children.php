<?php

namespace Cere\Survey;

trait Children {

    public function removeGrandsonModels () {

    }

    public function insertChildrens(&$questions)
    {
        return $questions->map(function($question) use (&$questions) {

            if ($question->byRules->isEmpty()) {
                return true;
            }

            $children = $question->byRules->first(function($index, $rule) {
                return $rule->is->expression == 'children';
            });

            $parameter = $children->is->parameters->first();

            switch ($parameter->type) {
                case 'answer':
                    $questions->forget($question->id);

                    //$questions[$parameter->question]->answers->keyBy('id')->get($parameter->answer)->childrens->push($question);
                    break;

                case 'question':
                    $questions->forget($question->id);

                    if (isset($questions[$parameter->question])) {
                        $questions[$parameter->question]->childrens->push($question);
                    }

                    break;
            }

            return  $children;
        });
    }

    public function setChildren($question)
    {
        $childrenRule = $this->answer->childrenRule();

        $rule = $childrenRule->getResults() ?: $childrenRule->create([]);

        $rule->questions()->attach([$question->id]);
    }

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

}