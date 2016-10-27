<?php

namespace Plat\Survey;

use Plat\Eloquent\Survey as SurveyORM;

class Node {    

    protected $parent;

    protected $node;

    function __construct($parent, SurveyORM\Node $node)
    {
        $this->parent = $parent;
        $this->node = $node;
    }

    public static function create($parent, array $attributes)
    {
        $node = $parent->childrenNodes()->save(new SurveyORM\Node($attributes));         

        $node->questions()->save(new SurveyORM\Question([]));

        return new static($parent, $node);
    }

    public static function make(SurveyORM\Node $node)
    {
        return $node ? new static($node->parent, $node) : NULL;
    }

    public static function find($id)
    {
        $node = SurveyORM\Node::find($id);

        return $node ? self::make($node) : NULL;
    }

    public function delete()
    {
        $this->node->answers()->delete();

        $this->node->questions()->delete();

        $deleted = $this->node->delete();

        return $deleted;
    }

    public function getModel()
    {
        return $this->node->load(['questions', 'answers']);
    }

    public function childrenModels()
    {
        return $this->node->answers()->has('childrenNodes')->get();
    }

    public function rules()
    {
        $rule = $this->node->childrenRule;

        return $rule ? $rule->node : \Illuminate\Database\Eloquent\Collection::make([]);
    }

    public function getParent()
    {
        if ($this->node->byRules->isEmpty()) {
            return Book::make($this->book);
        }

        $children = $this->node->byRules->first(function($index, $rule) {
            return $rule->is->expression == 'children';
        });

        $parameter = $children->is->parameters->first();

        switch ($parameter->type) {
            case 'answer':
                $parent = Answer::find($parameter->answer);
                break;

            case 'question':
                $parent = Question::find($parameter->question);
                break;
        }

        return $parent;
    }

    public function setChildren($node)
    {
        $childrenRule = $this->node->childrenRule();

        $rule = $childrenRule->getResults() ?: $childrenRule->create([]);

        $rule->node()->attach([$node->id]);
    }

}