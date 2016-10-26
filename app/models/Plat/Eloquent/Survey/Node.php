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

    public function getTypeAttribute($value)
    {
        $types = [
            'explain'  => ['name' => 'explain',  'question' => false, 'answers' => false, 'title' =>'說明文字',   'icon' =>'info-outline'],
            'select'   => ['name' => 'select',   'question' => true, 'answers' => true, 'title' =>'下拉式選單', 'icon' =>'arrow-drop-down-circle'],
            'radio'    => ['name' => 'radio',    'question' => true, 'answers' => true, 'title' =>'單選題',     'icon' =>'radio-button-checked'],
            'checkbox' => ['name' => 'checkbox', 'question' => true, 'answers' => false, 'title' =>'複選題',     'icon' =>'check-box'],
            'scale'    => ['name' => 'scale',    'question' => true, 'answers' => true, 'title' =>'量表題',     'icon' =>'list'],
            'text'     => ['name' => 'text',     'question' => true, 'answers' => false, 'title' =>'文字填答',   'icon' =>'mode-edit'],
            'list'     => ['name' => 'list',     'question' => false, 'answers' => false, 'title' =>'題組',       'icon' =>'sitemap', 'disabled' => true],
            'textarea' => ['name' => 'textarea', 'question' => false, 'answers' => false, 'title' =>'文字欄位',   'disabled' => true],
            'table'    => ['name' => 'table',    'question' => false, 'answers' => false, 'title' =>'表格',       'disabled' => true],
            'jump'     => ['name' => 'jump',     'question' => false, 'answers' => false, 'title' =>'開啟題本',   'type' =>'rule'],
        ];

        return $types[$value];
    }

}