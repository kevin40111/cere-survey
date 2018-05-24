<?php

namespace Cere\Survey\Eloquent;

use Eloquent;
use Plat\Eloquent\Upload;

class Node extends Eloquent
{
    use TreeTrait;

    use PositionTrait;

    protected $connection = 'survey';

    protected $table = 'survey_nodes';

    public $timestamps = false;

    protected $fillable = ['type', 'title', 'position'];

    protected $attributes = ['title' => ''];

    protected $appends = ['class'];

    public function parent()
    {
        return $this->morphTo();
    }

    public function childrenNodes()
    {
        return $this->morphMany(Node::class, 'parent');
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

    public function getTypeAttribute($value)
    {
        return $value;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function($node) {

            $node->answers()->delete();

            $node->questions()->delete();

        });
    }

    public function skipers()
    {
        return $this->hasMany(Rule\Skiper::class);
    }

    public function guarders()
    {
        return $this->morphMany(Rule\Guarder::class, 'survey_rule_guarder', 'target_type', 'target_id');
    }

    public function skiper()
    {
        return $this->morphOne(Rule\Skiper::class, 'effect');
    }

    public function images()
    {
        return $this->belongsToMany(Upload::class, 'image_node');
    }

    public function siblings()
    {
        return $this->parent->childrenNodes();
    }
}
