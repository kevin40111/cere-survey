<?php

namespace Plat\Eloquent\Survey;

use Eloquent;

class ApplicableOption extends Eloquent {

    protected $table = 'survey_applicable_options';

    protected $fillable = array('survey_applicable_option_id', 'survey_applicable_option_type');

    protected $appends = array('selected');

    public function surveyApplicableOption()
    {
        return $this->morphTo();
    }

    public function appliedOption()
    {
        return $this->hasOne('Plat\Eloquent\Survey\AppliedOption', 'applicable_option_id', 'id');
    }

    public function getSelectedAttribute()
    {
        return isset($this->appliedOption);
    }
}