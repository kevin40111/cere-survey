<?php

namespace Cere\Survey\Eloquent;

use Eloquent;

class Rule extends Eloquent {

    protected $connection = 'survey';

    protected $table = 'survey_rules';

    public $timestamps = true;

    protected $fillable = ['type', 'page_id'];

    public function effect()
    {
        return $this->morphTo();
    }

    public function operations()
    {
        return $this->morphMany(Rule\Operation::class, 'survey_operation', 'effect_type', 'effect_id');
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
