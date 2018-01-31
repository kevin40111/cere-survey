<?php

namespace Cere\Survey\Eloquent;

use Eloquent;

class ApplicableOption extends Eloquent {

    protected $connection = 'survey';

    protected $table = 'survey_applicable_options';

    protected $fillable = array('survey_applicable_option_id', 'survey_applicable_option_type');

    public function surveyApplicableOption()
    {
        return $this->morphTo();
    }
}