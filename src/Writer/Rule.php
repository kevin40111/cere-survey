<?php

namespace Cere\Survey\Writer;

use Cere\Survey\Eloquent as SurveyORM;

class Rule
{
    public $pass;

    public $effects = [];

    function __construct($answers)
    {
        $this->answers = $answers;
    }

    public static function answers($answers)
    {
        return new self($answers);
    }

    public function checkLimit($question)
    {
        $limit = $question->node->rule()->where('type', 'limit')->first();

        if ($limit) {
            $limit_size = $limit->expressions[0]['value'];

            $questions = $question->node->questions()->get(['id']);
            foreach ($questions as $question) {
                $this->answers[$question->id] == 1 && $limit_size--;
                if($limit_size <= 0) {
                    return true;
                }
            }

        }

        return false;
    }

    public function compare($rule)
    {
        if ($rule) {
            $expressions = $rule->expressions;
            $result = 'return ';
            foreach ($expressions as $expression) {
                if (isset($expression['compareLogic'])) {
                    $result = $result.$expression['compareLogic'];
                }
                $result = $result.'(';
                foreach ($expression['conditions'] as $condition) {
                    if (isset($condition['compareOperator'])) {
                        $result = $result.$condition['compareOperator'];
                    }
                    $question = is_null($this->answers[$condition['question']]) ? 'null' : $this->answers[$condition['question']];
                    $result = $result.$question.$condition['logic'].$condition['value'];
                }
                $result = $result.')';
            }
            $result = $result.';';

            return eval($result);

        } else {

            return false;
        }
    }
}
