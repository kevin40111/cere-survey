<?php

namespace Cere\Survey;

use Cere\Survey\Eloquent as SurveyORM;
use Cere\Survey\Eloquent\Field\Field;

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

    public static function find($rule_id)
    {
        $rule = SurveyORM\Rule::find($rule_id);
        $class = $rule->effect_type;
        $target = $class::find($rule->effect_id);

        return new self($target);
    }

    public function getRule()
    {
        return $this->target->rule ? $this->target->rule : new SurveyORM\Rule(['expressions' => [['conditions' => [['compareType' => 'question']]]]]);
    }

    public function saveExpressions($expressions, $type, $page_id)
    {
        $rule = SurveyORM\Rule::where('effect_id', $this->target->id)->where('type', $type)->first();
        if ($rule == null) {
            $rule = $this->target->rule()->save(new SurveyORM\Rule(['expressions' => $expressions, 'type' => $type, 'page_id' => $page_id]));
        } else {
            $rule->update(['expressions' => $expressions, 'page_id' => $page_id]);
        }

        $type == 'jump' && $this->saveRulesFactor($expressions, $rule);

        return $rule;
    }

    public function deleteRule($rule_id = null)
    {
        $rule_id = isset($rule_id) ? $rule_id : $this->target->rule->id;
        SurveyORM\SurveyRuleFactor::where('rule_id', $rule_id)->delete();

        SurveyORM\Rule::find($rule_id)->delete();
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