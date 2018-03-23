<?php

namespace Cere\Survey\Eloquent;

use Illuminate\Database\Eloquent\Collection;

trait PositionTrait
{
    abstract public function siblings();

    public function newCollection(array $models = array())
    {
        $collection = new Collection($models);

        return $collection->sortBy('position')->values();
    }

    public function move($offset)
    {
        if ($offset < 0) {
            $this->siblings()->where('position', '>=', $this->position + $offset)->where('position', '<', $this->position)->increment('position');
        }

        if ($offset > 0) {
            $this->siblings()->where('position', '<=', $this->position + $offset)->where('position', '>', $this->position)->decrement('position');
        }

        $this->position = $this->position + $offset;

        return $this->save();
    }

    public function next()
    {
        return $this->siblings()->where('position', $this->position + 1)->first();
    }

    protected static function bootPositionTrait()
    {
        static::creating(function ($item) {

            $item->siblings()->where('position', '>=', $item->position)->increment('position');

        });

        static::deleted(function ($item) {

            $item->siblings()->where('position', '>=', $item->position)->decrement('position');

        });
    }
}
