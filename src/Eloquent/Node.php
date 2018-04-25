<?php

namespace Cere\Survey\Eloquent;

use Eloquent;
use Plat\Eloquent\Upload;

class Node extends Eloquent {

    use \Cere\Survey\Tree;

    use PositionTrait;

    protected $connection = 'survey';

    protected $table = 'survey_nodes';

    public $timestamps = false;

    protected $fillable = ['type', 'title', 'position'];

    protected $attributes = ['title' => ''];

    protected $appends = ['class'];

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
        return $this->hasMany(Question::class);
    }

    public function answers()
    {
        return $this->morphMany(Answer::class, 'belong');
    }

    public function getClassAttribute()
    {
        return self::class;
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

    protected static function boot()
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
        return $this->hasMany('Cere\Survey\Eloquent\Rule', 'page_id')->where('type', 'jump');
    }

    public function siblings()
    {
        return $this->parent->childrenNodes();
    }
}