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

    public function sortByPrevious(array $relations)
    {
        foreach ($relations as $relation) {

            $nextNodes = $this->$relation->keyBy('previous_id');

            $nodes = \Illuminate\Database\Eloquent\Collection::make($nextNodes->isEmpty() ? [] : [$nextNodes['']]);

            $nextNodes->each(function($node) use ($nextNodes, &$nodes) {
                $previous_id = $nodes[count($nodes)-1]->id;
                if (isset($nextNodes[$previous_id]))
                    $nodes->add($nextNodes[$previous_id]);
            });

            $this->setRelation($relation, $nodes);
        }

        return $this;
    }

    public function after($previous_id)
    {
        $next = isset($previous_id) ? self::find($previous_id)->next : false;

        if ($next) {
            $next->update(['previous_id' => $this->id]);
        }

        $this->update(['previous_id' => $previous_id]);

        return $this;
    }

    public function moveUp()
    {
        $top_id = $this->previous->previous ? $this->previous->previous->id : NULL;

        $this->next && $this->next->after($this->previous->id);

        $this->previous->after($this->id);

        $this->after($top_id);

        return $this;
    }

    public function moveDown()
    {
        $this->next->moveUp();

        return $this;
    }

}
