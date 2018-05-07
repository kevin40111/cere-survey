<?php

namespace Cere\Survey\Eloquent\Rule;

use Eloquent;

class Factor extends Eloquent
{
    protected $connection = 'survey';

    protected $table = 'survey_rule_factors';

    public $timestamps = false;

    protected $fillable = ['value'];

    public function target()
    {
        return $this->morphTo();
    }
}
