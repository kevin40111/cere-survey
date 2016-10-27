<?php

namespace Plat\Eloquent\Survey;

use Eloquent;

class Question extends Eloquent {

    use \Plat\Survey\Tree;

    protected $table = 'survey_questions';

    public $timestamps = false;

    protected $fillable = ['title', 'previous_id'];

    protected $attributes = ['title' => ''];

    public function node()
    {
        return $this->hasOne('Plat\Eloquent\Survey\Node', 'id', 'node_id');
    }

    public function next()
    {
        return $this->hasOne('Plat\Eloquent\Survey\Question', 'previous_id', 'id');
    }

    public function previous()
    {
        return $this->hasOne('Plat\Eloquent\Survey\Question', 'id', 'previous_id');
    }

    public function getRequiredAttribute($value)
    {
        return (bool)$value;
    }

}