<?php

namespace Plat\Survey;

use Plat\Eloquent\Survey as SurveyORM;

class RuleRepository
{
    function __construct($target)
    {
        $this->target = $target;
    }

    public static function target($target)
    {
        return new self($target);
    }

    public function getRule()
    {
        return $this->target->rule ? $this->target->rule : new SurveyORM\Rule(['expressions' => [['conditions' => [['compareType' => 'question']]]]]);
    }

    public function saveExpressions($expressions)
    {
        if ($this->target->rule == null) {
            $rule = $this->target->rule()->save(new SurveyORM\Rule(['expressions' => $expressions]));
        } else {
            $rule = $this->target->rule;
            $rule->update(['expressions' => $expressions]);
        }

        return $rule;
    }
}