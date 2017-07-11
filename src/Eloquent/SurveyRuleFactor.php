<?php

namespace Cere\Survey\Eloquent;

use Eloquent;
use Cere\Survey\Eloquent\Field\Field;

class SurveyRuleFactor extends Eloquent
{
    protected $table = 'survey_rule_factor';

    public $timestamps = false;

    protected $fillable = ['rule_relation_factor', 'rule_id'];

    public function rule()
    {
        return $this->belongsTo('Cere\Survey\Eloquent\Rule', 'rule_id', 'id');
    }

    public function question()
    {
        return $this->belongsTo(Field::class, 'rule_relation_factor', 'id');
    }
}
