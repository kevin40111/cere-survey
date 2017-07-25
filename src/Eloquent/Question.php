<?php

namespace Cere\Survey\Eloquent;

use Eloquent;

class Question extends Eloquent {

    use \Cere\Survey\Tree;

    protected $table = 'survey_questions';

    public $timestamps = false;

    protected $fillable = ['title', 'previous_id'];

    protected $attributes = ['title' => ''];

    protected $appends = ['class', 'relation'];

    public function node()
    {
        return $this->hasOne('Cere\Survey\Eloquent\Node', 'id', 'node_id');
    }

    public function next()
    {
        return $this->hasOne('Cere\Survey\Eloquent\Question', 'previous_id', 'id');
    }

    public function previous()
    {
        return $this->hasOne('Cere\Survey\Eloquent\Question', 'id', 'previous_id');
    }

    public function childrenNodes()
    {
        return $this->morphMany('Cere\Survey\Eloquent\Node', 'parent');
    }

    public function getClassAttribute()
    {
        return self::class;
    }

    public function getRelationAttribute()
    {
        return 'questions';
    }

    public function getRequiredAttribute($value)
    {
        return (bool)$value;
    }

    public function rule()
    {
        return $this->morphOne('Cere\Survey\Eloquent\Rule', 'effect');
    }

}