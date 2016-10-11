<?php

namespace Plat\Eloquent\Survey;

use Eloquent;

class Rule extends Eloquent {

    protected $table = 'survey_rules';

    public $timestamps = true;

    protected $fillable = array('expression', 'answer_id', 'warning');

    protected $appends = array('is');

    public function questions()
    {
        return $this->morphedByMany('Plat\Eloquent\Survey\Question', 'survey_set_skips');
    }

    public function skipAnswers()
    {
        return $this->morphedByMany('Plat\Eloquent\Survey\Answer', 'survey_set_skips');
    }

    public function jumpBook()
    {
        return $this->morphedByMany('Plat\Eloquent\Survey\Book', 'survey_set_skips');
    }

    public function openWave()
    {
        return $this->morphedByMany('Plat\Eloquent\Survey\Wave', 'survey_set_skips');
    }

    public function answers()
    {
        return $this->belongsToMany('Plat\Eloquent\Survey\Answer', 'interview_answers_in_rule', 'rule_id', 'answer_id' );
    }

    public function getIsAttribute()
    {
        $condition = (object)json_decode($this->expression);
        $condition->parameters = \Illuminate\Database\Eloquent\Collection::make($condition->parameters);
        $this->attributes['is'] = $condition;
        return $this->attributes['is'];
    }

    // public function setExpressionAttribute($expression)
    // {
    //     ddd($expression);
    //     $this->attributes['expression'] = json_encode($expression);
    // }

}