<?php

namespace Cere\Survey\Eloquent;

use Eloquent;

class Rule extends Eloquent {

    protected $connection = 'survey';

    protected $table = 'survey_rules';

    public $timestamps = true;

    protected $fillable = ['method', 'page_id'];

    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    public function effect()
    {
        return $this->morphTo();
    }

    public function operations()
    {
        return $this->morphMany(Rule\Operation::class, 'survey_operation', 'effect_type', 'effect_id');
    }

    public function toJsonLogic()
    {
        return $this->simplify($this->operations->first());
    }

    protected function simplify($operation)
    {
        if ($operation->factor) {
            $var = $operation->factor->target instanceof Question ? $operation->factor->target->field->id : $operation->factor->target->id;
            return [$operation->operator => [['var' => $var], $operation->factor->value]];
        }

        $values = array_map(function ($operation) {
            return $this->simplify($operation);
        }, $operation->operations->all());

        return [$operation->operator => $values];
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
