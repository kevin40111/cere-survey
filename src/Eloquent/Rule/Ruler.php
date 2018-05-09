<?php

namespace Cere\Survey\Eloquent\Rule;

use Eloquent;
use Cere\Survey\Eloquent\Question;

abstract class Ruler extends Eloquent
{
    public function operations()
    {
        return $this->morphMany(Operation::class, 'survey_operation', 'effect_type', 'effect_id');
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
}
