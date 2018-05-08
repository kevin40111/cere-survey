<?php

namespace Cere\Survey\Writer;

use Cere\Survey\Eloquent\Question;
use JWadhams\JsonLogic;

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
        return JsonLogic::apply($rule->toJsonLogic(), $this->answers);
    }
}
