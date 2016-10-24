<?php

namespace Plat\Survey;

use Plat\Eloquent\Survey as SurveyORM;

class Node {

    protected $book;

    protected $node;

    function __construct(SurveyORM\Book $book, SurveyORM\Node $node)
    {
        $this->book = $book;
        $this->node = $node;
    }

    public static function create(SurveyORM\Book $book, array $attributes, $parent)
    {
        //$ff = new SurveyORM\Node($attributes);
        //dd($ff->previous);

        $first = $book->nodes()->whereNull('previous_id')->first();

        $previous = Node::find($attributes['previous_id']);

        $next = $previous ? $previous->next() : $first;

        $node = $book->nodes()->save(new SurveyORM\Node($attributes));

        $node->questions()->save(new SurveyORM\Question([]));

        $node->next()->save($next);

        //$next && $next->update(['previous_id' => $node->id]);

        //$parent['class']::find($parent['id'])->setChildren($question);

        return new static($book, $node);
    }

    public static function make(SurveyORM\Node $node)
    {
        return $node ? new static($node->book, $node) : NULL;
    }

    public static function find($id)
    {
        $node = SurveyORM\Node::find($id);

        return $node ? self::make($node) : NULL;
    }

    public function update(array $attributes)
    {
        foreach ($attributes as $key => $value)
        {
            $this->node->$key = $value;
        }

        $this->node->save();

        return $this;
    }

    public function delete()
    {
        $this->next() && $this->next()->update(['previous_id' => $this->node->previous_id]);

        $this->node->answers()->delete();

        $this->node->questions()->delete();

        $deleted = $this->node->delete();

        return $deleted;
    }

    public function getModel()
    {
        return $this->node->load(['questions', 'answers']);
    }

    public function next()
    {
        return $this->node->next ? Node::make($this->node->next) : NULL;
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

    public function getChildrenModels()
    {
        $rule = $this->node->childrenRule;

        return $rule ? $rule->node : \Illuminate\Database\Eloquent\Collection::make([]);
    }

    public function getQuestionModels()
    {
        $questions = $this->getChildrenModels();

        return $questions;//$this->removeChildrenModels($questions);
    }

    public function sort($anchor)
    {
        $index = 0;
        $this->getParent()->getChildrenModels()->except($this->node->id)->sortBy('sorter')->each(function($node) use (&$index, $anchor) {
            $node->sorter = $index >= $anchor ? $index+1 : $index;
            $node->save();
            $index++;
        });

        $this->node->sorter = $anchor;

        $this->node->save();

        return $this;
    }

    public function setChildren($node)
    {
        $childrenRule = $this->node->childrenRule();

        $rule = $childrenRule->getResults() ?: $childrenRule->create([]);

        $rule->node()->attach([$node->id]);
    }

}