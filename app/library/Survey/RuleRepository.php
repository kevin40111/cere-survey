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

        $this->saveRulesFactor($expressions, $rule);

        return $rule;
    }

    public function deleteRule()
    {
        SurveyORM\SurveyRuleFactor::where('rule_id', $this->target->rule->id)->delete();

        $this->target->rule()->delete();
    }

    protected function saveRulesFactor($expressions, $rule)
    {
        SurveyORM\SurveyRuleFactor::where('rule_id', $rule->id)->delete();

        foreach ($expressions as $expression) {
            foreach ($expression['conditions'] as $condition) {
                if (isset($condition['question']) && !SurveyORM\SurveyRuleFactor::where('rule_relation_factor', $condition['question'])->where('rule_id', $rule->id)->exists()) {
                    SurveyORM\SurveyRuleFactor::create(['rule_relation_factor' => $condition['question'], 'rule_id' => $rule->id]);   
                }
            }
        }
    }
}