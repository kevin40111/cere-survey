<?php

namespace Cere\Survey\Writer;

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
        $types = ['Text', 'Checkbox', 'Radio', 'Gear'];

        $node->type = strtolower($node->type);

        $type = 'Cere\Survey\Writer\Fillers\\' . (in_array(ucfirst($node->type), $types) ? ucfirst($node->type) : 'Radio');

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

    public function getSkips()
    {
        $skips = [];

        foreach ($this->fillers as $filler) {
            $skips += $filler->getSkips();
        }

        return $skips;
    }
}
