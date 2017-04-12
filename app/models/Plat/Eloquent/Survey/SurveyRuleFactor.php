<?php

namespace Plat\Eloquent\Survey;

use Eloquent;

class SurveyRuleFactor extends Eloquent
{
    protected $table = 'survey_rule_factor';

    public $timestamps = false;

    protected $fillable = ['rule_relation_factor', 'rule_id'];

    public function rule()
    {
        return $this->belongsTo('Plat\Eloquent\Survey\Rule', 'rule_id', 'id');   
    }

    public function questions()
    {
        return $this->belongsTo('Plat\Eloquent\Survey\Question', 'rule_relation_factor', 'id');
    }
}
