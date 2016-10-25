<?php

namespace Plat\Eloquent\Survey;

use Eloquent;

class Question extends Eloquent {

    protected $table = 'survey_questions';

    public $timestamps = false;

    protected $fillable = ['title'];

    protected $attributes = ['title' => ''];

    public function node()
    {
        return $this->hasOne('Plat\Eloquent\Survey\Node', 'id', 'node_id');
    }

    public function getRequiredAttribute($value)
    {
        return (bool)$value;
    }

}