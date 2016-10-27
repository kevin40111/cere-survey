<?php

namespace Plat\Survey;

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

}