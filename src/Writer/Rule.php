<?php

namespace Cere\Survey\Writer;

use JWadhams\JsonLogic;
use Cere\Survey\Eloquent\Rule\Ruler;

class Rule
{
    protected $rule;

    function __construct(Ruler $ruler)
    {
        $this->ruler = $ruler;
    }

    public static function instance($ruler)
    {
        return new self($ruler);
    }

    public function lessThan($amount)
    {
        return JsonLogic::apply($this->ruler->toJsonLogic(), [$this->ruler->target->id => $amount]);
    }

    public function compare($fields)
    {
        return JsonLogic::apply($this->ruler->toJsonLogic(), $fields);
    }

    public function maxLength($answer)
    {
        return JsonLogic::apply($this->ruler->toJsonLogic(), [$this->ruler->target->field->id => mb_strlen($answer)]);
    }
}
