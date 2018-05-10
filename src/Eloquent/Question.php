<?php

namespace Cere\Survey\Eloquent;

use Eloquent;
use Cere\Survey\Eloquent\Field\Field;

class Question extends Eloquent
{
    use \Cere\Survey\Tree;

    use PositionTrait;

    protected $connection = 'survey';

    protected $table = 'survey_questions';

    public $timestamps = false;

    protected $fillable = ['title', 'position'];

    protected $attributes = ['title' => ''];

    protected $appends = ['class'];

    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    public function childrenNodes()
    {
        return $this->morphMany(Node::class, 'parent');
    }

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    public function getClassAttribute()
    {
        return self::class;
    }

    public function getRequiredAttribute($value)
    {
        return (bool)$value;
    }

    public function skiper()
    {
        return $this->morphOne(Rule\Skiper::class, 'effect');
    }

    public function effects()
    {
        return $this->morphToMany(Rule\Operation::class, 'target', 'survey_rule_factors');
    }

    public function siblings()
    {
        return $this->node->questions();
    }
}
