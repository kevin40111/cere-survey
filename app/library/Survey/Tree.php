<?php

namespace Plat\Survey;

trait Tree {

    public function getPaths()
    {
        $paths = $this->node ? $paths = $this->node->parent->getPaths()->add($this) : \Illuminate\Database\Eloquent\Collection::make([$this]);

        return $paths;
    }

    public function initNode()
    {
        if ($this->childrenNodes->isEmpty()) {
            Node::create($this, ['type' => 'explain', 'previous_id' => NULL]);

            $this->load('childrenNodes');
        }

        return $this;
    }

}
