<?php

namespace Cere\Survey\Eloquent;

use Eloquent;

class SurveyRuleFactor extends Eloquent
{
    protected $table = 'survey_rule_factor';

    public $timestamps = false;

    protected $fillable = ['rule_relation_factor', 'rule_id'];

    public function rule()
    {
        return $this->belongsTo('Cere\Survey\Eloquent\Rule', 'rule_id', 'id');
    }

    public function questions()
    {
        return $this->belongsTo('Cere\Survey\Eloquent\Question', 'rule_relation_factor', 'id');
    }
}
