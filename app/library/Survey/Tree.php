<?php

namespace Plat\Survey;
use Plat\Eloquent\Survey as SurveyORM;

trait Tree {

    public function getPaths()
    {
        $parent = is_a($this, 'Plat\Eloquent\Survey\Answer') ? $this->node->parent : $this->parent;

        $paths = $parent ? $parent->getPaths()->add($this) : \Illuminate\Database\Eloquent\Collection::make([$this]);

        return $paths;
    }

    public function sortByPrevious(array $relations)
    {
        foreach ($relations as $relation) {

            $nextNodes = $this->$relation->keyBy('previous_id');

            $last = $nextNodes->pull('', false);

            $nodes = \Illuminate\Database\Eloquent\Collection::make([]);

            while ($last) {

                $nodes->push($last);

                $last = $nextNodes->pull($last->id, false);

            }

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

    public function delete()
    {
        if ($this->next) {
            if ($this->previous == NULL) {
                $this->next->after(NULL);
            } else {
                $this->next->after($this->previous->id);
            }
        }
        parent::delete();
        return $this;
    }

}
