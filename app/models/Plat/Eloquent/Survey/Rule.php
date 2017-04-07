<?php

namespace Plat\Eloquent\Survey;

use Eloquent;

class Rule extends Eloquent {

    protected $table = 'survey_rules';

    public $timestamps = true;

    protected $fillable = array('expressions', 'answer_id', 'warning');

    public function questions()
    {
        return $this->morphedByMany('Plat\Eloquent\Survey\Question', 'survey_rule_effect');
    }

    public function skipAnswers()
    {
        return $this->morphedByMany('Plat\Eloquent\Survey\Answer', 'survey_rule_effect');
    }

    public function jumpBook()
    {
        return $this->morphedByMany('Plat\Eloquent\Survey\Book', 'survey_rule_effect');
    }

    public function openWave()
    {
        return $this->morphedByMany('Plat\Eloquent\Survey\Wave', 'survey_rule_effect');
    }

    public function answers()
    {
        return $this->belongsToMany('Plat\Eloquent\Survey\Answer', 'interview_answers_in_rule', 'rule_id', 'answer_id' );
    }

    // public function getIsAttribute()
    // {
    //     $condition = (object)json_decode($this->expression);
    //     $condition->parameters = \Illuminate\Database\Eloquent\Collection::make($condition->parameters);
    //     $this->attributes['is'] = $condition;
    //     return $this->attributes['is'];
    // }

    // public function setExpressionAttribute($expression)
    // {
    //     ddd($expression);
    //     $this->attributes['expression'] = json_encode($expression);
    // }

    public function setExpressionsAttribute($expressions)
    {
        $this->attributes['expressions'] = json_encode($expressions);
    }

    public function getExpressionsAttribute($expressions)
    {
        return json_decode($expressions, true);
    }

    public function factors()
    {
        return $this->hasMany('Plat\Eloquent\Survey\SurveyRuleFactor', 'rule_id');
    }

}