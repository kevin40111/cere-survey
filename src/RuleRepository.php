<?php

namespace Cere\Survey;

use Cere\Survey\Eloquent as SurveyORM;

class RuleRepository
{
    function __construct($operation)
    {
        $this->operation = $operation;
    }

    public static function target($operation)
    {
        return new self($operation);
    }

    public static function find($id)
    {
        $operation = SurveyORM\Rule\Operation::findOrFail($id);

        return new self($operation);
    }

    public function createFactor($factor, $target)
    {
        $factor = new SurveyORM\Rule\Factor($factor);

        $factor->target()->associate($target);
        $factor = $this->operation->factor()->save($factor);

        return $factor;
    }

    public static function updateFactorTarget($factor_id, $target)
    {
        $factor = SurveyORM\Rule\Factor::findOrFail($factor_id);

        $factor->target()->associate($target);
        $updated = $factor->save();

        return $updated;
    }

    public static function updateFactor($factor_id, $attributes)
    {
        $factor = SurveyORM\Rule\Factor::findOrFail($factor_id);

        $updated = $factor->update($attributes);

        return $updated;
    }

    public function explanation()
    {
        $operators = [' && ' => '而且', ' || ' => '或者'];
        $booleans = [' > ' => '大於', ' < ' => '小於', ' == ' => '等於', ' != ' => '不等於'];
        $expressions = $this->target->rule->expressions;

        $explanation = '';
        foreach ($expressions as $expression) {
            if (isset($expression['compareLogic'])) {
                $operator = $operators[$expression['compareLogic']];
                $explanation .= $operator;
            }
            $explanation .= ' ( ';
            foreach ($expression['conditions'] as $condition) {

                if (isset($condition['compareOperator'])) {
                    $operator = $operators[$condition['compareOperator']];
                    $explanation .= $operator;
                }

                $question = SurveyORM\Question::find($condition['question']);
                $boolean = $booleans[$condition['logic']];

                $answer = $condition['compareType'] == 'value' ? $condition['value'] : $question->node->answers()->where('value', $condition['value'])->first()->title;

                $explanation .= $question->title . $boolean . $answer;
            }
            $explanation .= ' ) ';
        }

        return $explanation;
    }
}