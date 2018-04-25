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

    public function rule()
    {
        return $this->morphOne(Rule::class, 'effect')->where('type', 'jump');
    }

    public function noneAboveRule()
    {
        return $this->morphOne(Rule::class, 'effect')->where('type', 'none_above');
    }

    public function affectRules()
    {
        return $this->belongsToMany(Rule::class, 'survey_rule_factor');
    }

    public function siblings()
    {
        return $this->node->questions();
    }
}
