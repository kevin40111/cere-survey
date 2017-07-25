<?php

namespace Cere\Survey\Eloquent;

use Eloquent;

class Answer extends Eloquent {

    use \Cere\Survey\Tree;

    protected $table = 'survey_answers';

    public $timestamps = false;

    protected $fillable = array('title', 'value', 'previous_id');

    protected $attributes = ['value' => '', 'title' => ''];

    protected $appends = ['class', 'relation'];

    public function node()
    {
        return $this->hasOne('Cere\Survey\Eloquent\Node', 'id', 'node_id');
    }

    public function next()
    {
        return $this->hasOne('Cere\Survey\Eloquent\Answer', 'previous_id', 'id');
    }

    public function previous()
    {
        return $this->hasOne('Cere\Survey\Eloquent\Answer', 'id', 'previous_id');
    }

    public function childrenNodes()
    {
        return $this->morphMany('Cere\Survey\Eloquent\Node', 'parent');
    }

    public function childrenRule()
    {
        return $this->hasOne('Cere\Survey\Eloquent\Rule', 'expression', 'children_expression');
    }

    public function rule()
    {
        return $this->morphOne('Cere\Survey\Eloquent\Rule', 'effect');
    }

    public function choose()
    {
        return $this->hasOne('Set\Choose', 'answer_id', 'id');
    }

    public function getClassAttribute()
    {
        return self::class;
    }

    public function getRelationAttribute()
    {
        return 'answers';
    }

    public function getChildrenExpressionAttribute()
    {
        $parameter = (object)[
            'type' => 'answer',
            'answer' => $this->id,
        ];
        $json = (object)['expression' => 'children', 'parameters' => [$parameter]];
        return json_encode($json);
    }

    public function getExpressionAttribute()
    {
        $parameter = (object)[
            'question' => $this->question_id,
            'answer' => $this->id,
        ];
        $json = (object)['expression' => 'r1', 'parameters' => [$parameter]];
        return json_encode($json);
    }

}