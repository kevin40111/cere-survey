<?php

namespace Cere\Survey\Eloquent\Extend;

use Eloquent;

class Reason extends Eloquent {

    protected $connection = 'survey';

    protected $table = 'survey_extend_reasons';

    public $timestamps = true;

    protected $fillable = ['content', 'verify_id'];
}
