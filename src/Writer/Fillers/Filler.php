<?php

namespace Cere\Survey\Writer\Fillers;

use Cere\Survey\Eloquent as SurveyORM;
use Cere\Survey\Writer\Rule;
use Illuminate\Database\Eloquent\Collection;

abstract class Filler
{
    public $messages;

    public $contents = [];

    protected $node;

    protected $original = [];

    protected $childrens = [];

    protected $skipers = [];

    protected $fillers = [];

    function __construct($node)
    {
        $this->node = $node;
    }

    /**
     * Set effected childrens.
     */
    abstract protected function setChildrens();

    /**
     * Decrement a answer was selected.
     */
    abstract protected function isChecked($target);

    public function set($contents)
    {
        $this->fill($contents);

        $this->preset();

        $this->guard();

        $this->setChildrens();

        $this->setSkipers();
    }

    protected function preset() {}

    public function getChildrens()
    {
        return $this->childrens;
    }

    protected function resetChildrens($target)
    {
        $value = $this->isChecked($target) ? null : '-8';

        $target->childrenNodes->each(function ($node) use ($value) {
            $contents = array_fill_keys($node->questions->lists('id'), $value);
            $this->add($node)->set($contents);
        });
    }

    protected function setSkipers()
    {
        $this->node->questions->load('effects.effect')->each(function ($question) {
            $question->effects->each(function ($operation) {
                $this->loadSkiper($operation);
            });
        });

        Collection::make($this->skipers)->load('node.questions.guarders', 'node.questions','node.answers', 'node.guarders')->each(function ($skiper) {
            $this->skipers[$skiper->id] = Rule::instance($skiper)->compare($this->contents + $this->original);
            $value = $this->skipers[$skiper->id] ? '-8' : null;
            $this->add($skiper->node)->set(array_fill_keys($skiper->node->questions->lists('id'), $value));
        });
    }

    private function loadSkiper($effected)
    {
        if ($effected instanceof SurveyORM\Rule\Skiper) {
            $this->skipers[$effected->id] = $effected;
        } else if ($effected instanceof SurveyORM\Rule\Operation) {
            $this->loadSkiper($effected->effect);
        }
    }

    protected function guard()
    {
        $this->node->guarders->sortBy('priority')->each(function ($guarder) {
            call_user_func([$this, $guarder->method], $guarder);
        });

        $this->node->questions->load('guarders')->each(function ($question) {
            $question->guarders->sortBy('priority')->each(function ($guarder) {
                call_user_func([$this, $guarder->method], $guarder);
            });
        });
    }

    public function getDirty()
    {
        $dirty = [];

        foreach ($this->contents as $id => $value) {
            if ($value !== $this->original[$id]) {
                $dirty[$id] = $value;
            }
        }

        foreach ($this->fillers as $filler) {
            $dirty += $filler->getDirty();
        }

        return $dirty;
    }

    public function getSkipers()
    {
        $skipers = $this->skipers;

        foreach ($this->fillers as $filler) {
            $skipers += $filler->getSkipers();// 先後順序
        }

        return $skipers;
    }

    public function getOriginal()
    {
        return $this->original;
    }

    public function syncOriginal($original)
	{
        $contents = array_fill_keys($this->node->questions->lists('id'), null);

        $this->original = $original + $contents;
    }

    protected function fill($contents)
	{
        $keys = $this->node->questions->lists('id');

        $contents += array_fill_keys($keys, null);

        $this->contents = array_intersect_key($contents, array_flip($keys));
    }

    private function isSkip($value)
    {
        return $value === '-8';
    }

    public static function instance($node, $original)
    {
        $type = __NAMESPACE__ . '\\' . ucfirst(strtolower($node->type));

        $filler = new $type($node);

        $filler->syncOriginal($original);

        return $filler;
    }

    public function add($node)
    {
        $original = $this->getDirty() + $this->original;

        $filler = static::instance($node, $original);

        array_push($this->fillers, $filler);

        return $filler;
    }
}
