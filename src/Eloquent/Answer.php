<?php

namespace Cere\Survey\Eloquent;

use Eloquent;
use DB;

class Answer extends Eloquent
{
    use TreeTrait;

    use PositionTrait;

    protected $connection = 'survey';

    protected $table = 'survey_answers';

    public $timestamps = false;

    protected $fillable = ['title', 'value', 'category_id', 'position'];

    protected $attributes = ['value' => '', 'title' => ''];

    protected $appends = ['class'];

    public function node()
    {
        return $this->morphTo('belong');
    }

    public function childrenNodes()
    {
        return $this->morphMany(Node::class, 'parent');
    }

    public function skiper()
    {
        return $this->morphOne(Rule\Skiper::class, 'effect');
    }

    public function getClassAttribute()
    {
        return self::class;
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

    protected static function boot()
    {
        parent::boot();

        static::created(function($answer) {

            $answer->siblings()->update(['value' => DB::raw('position')]);

        });

        static::updated(function($answer) {

            if ($answer->isDirty('position')) {
                $answer->siblings()->update(['value' => DB::raw('position')]);
            }

        });

        static::deleted(function($answer) {

            $answer->siblings()->update(['value' => DB::raw('position')]);

        });
    }

    public function siblings()
    {
        return $this->node->answers();
    }
}
