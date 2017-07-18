<?php

namespace Plat\Survey\Writer;

class Fill
{
    public $messages;

    public $fillers = [];

    function __construct($answers)
    {
        $this->answers = $answers;
    }

    public static function answers($answers)
    {
        return new static($answers);
    }

    public function node($node)
    {
        $type = 'Plat\Survey\Writer\Filler\\' . ($node->type == 'checkbox' ? ucfirst($node->type) : 'Radio');

        $filler = new $type($node, $this->answers);

        $this->add($filler);

        return $filler;
    }

    public function add($filler)
    {
        array_push($this->fillers, $filler);
    }

    public function getDirty()
    {
        $dirty = [];

        foreach ($this->fillers as $filler) {
            foreach ($filler->getDirty() as $id => $value) {
                $dirty[$id] = $value;
            }
        }

        return $dirty;
    }
}
