<?php

namespace Cere\Survey\Eloquent;

use Eloquent;
use Plat\Eloquent\Upload;
use Cere\Survey\Eloquent\Field\Field;

class Node extends Eloquent {

    use \Cere\Survey\Tree;

    protected $connection = 'survey';

    protected $table = 'survey_nodes';

    public $timestamps = false;

    protected $fillable = ['type', 'title', 'previous_id'];

    protected $attributes = ['title' => ''];

    protected $appends = ['class', 'relation'];

    public function book()
    {
        return $this->hasOne('Cere\Survey\Eloquent\Book', 'id', 'book_id');
    }

    public function parent()
    {
        return $this->morphTo();
    }

    public function childrenNodes()
    {
        return $this->morphMany('Cere\Survey\Eloquent\Node', 'parent');
    }

    public function questions()
    {
        return $this->hasMany(Field::class, 'node_id', 'id');
    }

    public function answers()
    {
        return $this->morphMany(Answer::class, 'belong');
    }

    public function next()
    {
        return $this->hasOne('Cere\Survey\Eloquent\Node', 'previous_id', 'id');
    }

    public function previous()
    {
        return $this->hasOne('Cere\Survey\Eloquent\Node', 'id', 'previous_id');
    }

    public function getClassAttribute()
    {
        return self::class;
    }

    public function getRelationAttribute()
    {
        return 'childrenNodes';
    }

    public function childrenRule()
    {
        return $this->hasOne('Cere\Survey\Eloquent\Rule', 'expression', 'children_expression');
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



        });

        static::deleted(function($node) {

            $node->answers()->delete();

            $node->questions()->delete();

        });
    }

    public function rule()
    {
        return $this->morphOne(Rule::class, 'effect')->where('type', 'jump');
    }

    public function limitRule()
    {
        return $this->morphOne(Rule::class, 'effect')->where('type', 'limit');
    }

    public function images()
    {
        return $this->belongsToMany(Upload::class, 'image_node');
    }

    public function pageRules()
    {
        return $this->hasMany('Cere\Survey\Eloquent\Rule', 'page_id');
    }
}