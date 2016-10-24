<?php

namespace Plat\Eloquent\Survey;

use Eloquent;

class Question extends Eloquent {

    protected $table = 'survey_questions';

    public $timestamps = false;

    protected $fillable = ['title'];

    protected $attributes = ['title' => ''];

    protected $appends = ['class'];

    public function node()
    {
        return $this->hasOne('Plat\Eloquent\Survey\Node', 'id', 'node_id');
    }

    public function byRules()
    {
        return $this->morphToMany('Plat\Eloquent\Survey\Rule', 'survey_rule_effect');
    }

    public function childrenRule()
    {
        return $this->hasOne('Plat\Eloquent\Survey\Rule', 'expression', 'children_expression');
    }

    public function scopePage($query, $page)
    {
        return $query->wherePage($page);
    }

    public function getRequiredAttribute($value)
    {
        return (bool)$value;
    }

    public function getSorterAttribute($value)
    {
        return (integer)$value;
    }

    public function getClassAttribute()
    {
        return \Plat\Survey\Question::class;
    }

    public function getChildrenExpressionAttribute()
    {
        $parameter = (object)[
            'type' => 'question',
            'question' => $this->id,
        ];
        $json = (object)['expression' => 'children', 'parameters' => [$parameter]];
        return json_encode($json);
    }

    public function getChildrensAttribute()
    {
        if (!isset($this->attributes['childrens'])) {
            $this->attributes['childrens'] = \Illuminate\Database\Eloquent\Collection::make([]);
        }

        return $this->attributes['childrens'];
    }

}