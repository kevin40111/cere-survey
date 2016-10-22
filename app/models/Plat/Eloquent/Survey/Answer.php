<?php

namespace Plat\Eloquent\Survey;

use Eloquent;

class Answer extends Eloquent {

    protected $table = 'survey_answers';

    public $timestamps = false;

    protected $fillable = array('title', 'value');

    protected $attributes = ['value' => '', 'title' => ''];

    protected $appends = ['class'];

    public function node()
    {
        return $this->hasOne('Plat\Eloquent\Survey\Node', 'id', 'node_id');
    }

    public function childrenRule()
    {
        return $this->hasOne('Plat\Eloquent\Survey\Rule', 'expression', 'children_expression');
    }

    public function rules()
    {
        return $this->morphToMany('Plat\Eloquent\Survey\Rule', 'survey_rule_effect');
    }

    public function rule()
    {
        return $this->hasOne('Plat\Eloquent\Survey\Rule', 'expression', 'expression');
    }

    public function choose()
    {
        return $this->hasOne('Set\Choose', 'answer_id', 'id');
    }

    public function getClassAttribute()
    {
        return \Plat\Survey\Answer::class;
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