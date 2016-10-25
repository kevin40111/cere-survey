<?php

namespace Plat\Eloquent\Survey;

use Eloquent;

class Node extends Eloquent {

    protected $table = 'survey_nodes';

    public $timestamps = false;

    protected $fillable = ['type', 'title', 'previous_id'];

    protected $attributes = ['title' => ''];

    public function book()
    {
        return $this->hasOne('Plat\Eloquent\Survey\Book', 'id', 'book_id');
    }

    public function parent()
    {
        return $this->morphTo();
    }

    public function questions()
    {
        return $this->hasMany('Plat\Eloquent\Survey\Question', 'node_id', 'id');
    }

    public function answers()
    {
        return $this->hasMany('Plat\Eloquent\Survey\Answer', 'node_id', 'id');
    }

    public function next()
    {
        return $this->hasOne('Plat\Eloquent\Survey\Node', 'previous_id', 'id');
    }

    public function previous()
    {
        return $this->hasOne('Plat\Eloquent\Survey\Node', 'id', 'previous_id');
    }

    public function byRules()
    {
        return $this->morphToMany('Plat\Eloquent\Survey\Rule', 'survey_rule_effect');
    }

    public function childrenRule()
    {
        return $this->hasOne('Plat\Eloquent\Survey\Rule', 'expression', 'children_expression');
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