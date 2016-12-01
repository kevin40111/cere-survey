<?php

namespace Plat\Eloquent\Survey;

use Eloquent;

class Question extends Eloquent {

    use \Plat\Survey\Tree;

    protected $table = 'survey_questions';

    public $timestamps = false;

    protected $fillable = ['title', 'previous_id'];

    protected $attributes = ['title' => ''];

    protected $appends = ['class', 'relation'];

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

    public function childrenNodes()
    {
        return $this->morphMany('Plat\Eloquent\Survey\Node', 'parent');
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

}