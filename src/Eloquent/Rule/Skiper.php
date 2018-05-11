<?php

namespace Cere\Survey\Eloquent\Rule;

use Cere\Survey\Eloquent\Node;

class Skiper extends Ruler
{
    protected $connection = 'survey';

    protected $table = 'survey_rule_skipers';

    public $timestamps = false;

    protected $fillable = [];

    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    public function effect()
    {
        return $this->morphTo();
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($rule) {

            $rule->operations->each(function ($operation) {
                $operation->delete();
            });

        });
    }
}
