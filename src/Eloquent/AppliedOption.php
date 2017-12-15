<?php

namespace Cere\Survey\Eloquent;

use Eloquent;

class AppliedOption extends Eloquent {

    protected $connection = 'survey';

    protected $table = 'survey_applied_options';

    protected $fillable = array('application_id', 'apply_option_id');

}