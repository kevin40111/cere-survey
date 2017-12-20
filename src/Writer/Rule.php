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
            $limit_size = $limit->expressions[0]['conditions'];

            $limit_size = $limit_size[0]['value'];

            $questions = $question->node->questions()->get(['id']);

            foreach ($questions as $question) {
                $this->answers->{$question->id} == 1 && $limit_size--;
                if($limit_size <= 0) {
                    return true;
                }
            }

        }

        return false;
    }

    /**
     * Compare rules.
     *
     * @return Response
     */
    public function effect($question_id)
    {
        $rules = SurveyORM\SurveyRuleFactor::where('rule_relation_factor', $question_id)->get()->groupBy('rule_id')->keys();

        return SurveyORM\Rule::find($rules)->map(function ($rule) {
            $pass = $this->compare($rule);
            $questions = [];
            if ($rule->effect_type === SurveyORM\Node::class) {
                $questions = array_merge($questions, $rule->effect->questions->all());
            }

            if ($rule->effect_type === SurveyORM\Question::class) {
                array_push($questions, $rule->effect);
            }

            return ['pass' => $pass, 'type' => $rule->type, 'questions' => $questions];
        });
    }

    public function skips($nodes)
    {
        return $nodes->map(function ($node) {
            $pass = $this->compare($node->rule);

            return ['id' => $node->id, 'pass' => $pass];
        })->lists('pass', 'id');
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
