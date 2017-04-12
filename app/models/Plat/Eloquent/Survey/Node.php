<?php

namespace Plat\Eloquent\Survey;

use Eloquent;

class Node extends Eloquent {

    use \Plat\Survey\Tree;

    protected $table = 'survey_nodes';

    public $timestamps = false;

    protected $fillable = ['type', 'title', 'previous_id'];

    protected $attributes = ['title' => ''];

    protected $appends = ['class', 'relation'];

    public function book()
    {
        return $this->hasOne('Plat\Eloquent\Survey\Book', 'id', 'book_id');
    }

    public function parent()
    {
        return $this->morphTo();
    }

    public function childrenNodes()
    {
        return $this->morphMany('Plat\Eloquent\Survey\Node', 'parent');
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

    public function getClassAttribute()
    {
        return self::class;
    }

    public function getRelationAttribute()
    {
        return 'childrenNodes';
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

    // public function getChildrenNodesAttribute()
    // {
    //     $this->load('childrenNodes');
    //     ddd($this->childrenNodes);

    //     return $this->attributes['childrenNodes'];
    // }

    public function getChildrensAttribute()
    {
        if (!isset($this->attributes['childrens'])) {
            $this->attributes['childrens'] = \Illuminate\Database\Eloquent\Collection::make([]);
        }

        return $this->attributes['childrens'];
    }

    public function getTypeAttribute($value)
    {
        return $value;
        return $this->types[$value];
    }

    public static function boot()
    {
        parent::boot();

        static::created(function($node) {

            if ($node->type != 'explain' && $node->type != 'page') {
                $node->questions()->save(new Question([]));
            }

        });

        static::deleted(function($node) {

            $node->answers()->delete();

            $node->questions()->delete();

        });
    }

    public function rule()
    {
        return $this->morphOne('Plat\Eloquent\Survey\Rule', 'effect');
    }

}