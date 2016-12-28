<?php

namespace Plat\Eloquent\Survey;

use Eloquent;

class AppliedOption extends Eloquent {

    protected $table = 'survey_applied_options';

    protected $fillable = array('application_id', 'apply_option_id');

}