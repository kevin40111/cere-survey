<?php

namespace Cere\Survey\Eloquent\Rule;

use Eloquent;

class Operation extends Eloquent {

    protected $connection = 'survey';

    protected $table = 'survey_rule_operations';

    public $timestamps = false;

    protected $fillable = ['operator'];

    public function effect()
    {
        return $this->morphTo();
    }

    public function operations()
    {
        return $this->morphMany(static::class, 'survey_operation', 'effect_type', 'effect_id');
    }

    public function factor()
    {
        return $this->hasOne(Factor::class);
    }

    public function getOperatorAttribute($operator)
    {
        if (in_array($operator, ['and', 'or'])) {
            $this->load('operations');
        } else {
            $this->load('factor.target');
        }

        return $operator;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($operation) {

            $operation->operations->each(function ($operation) {
                $operation->delete();
            });

            $operation->factor()->delete();

        });
    }
}
