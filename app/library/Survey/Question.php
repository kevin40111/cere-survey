<?php

namespace Plat\Survey;

use Plat\Eloquent\Survey as SurveyORM;

class Question {

    protected $book;

    protected $question;

    function __construct(SurveyORM\Book $book, SurveyORM\Question $question)
    {
        $this->book = $book;
        $this->question = $question;
    }

    public static function create(SurveyORM\Book $book, array $attributes, $parent)
    {        
        $question = $book->questions()->save(new SurveyORM\Question($attributes));        

        $parent['class']::find($parent['id'])->setChildren($question);

        return new static($book, $question);
    }

    public static function find($id)
    {
        $question = SurveyORM\Question::find($id);

        $book = $question->book;

        return new static($book, $question);
    }

    public function update(array $attributes)
    {
        foreach ($attributes as $key => $value)
        {
            $this->question->$key = $value;
        }

        $this->question->save();

        return $this;
    }

    public function delete()
    {
        $this->question->answers()->delete();

        //$this->question->questions()->delete(); delete childrens

        $deleted = $this->question->delete();

        return $deleted;
    }

    public function getParent()
    {
        if ($this->question->byRules->isEmpty()) {
            return Book::make($this->book);
        }

        $children = $this->question->byRules->first(function($index, $rule) {
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
        $rule = $this->question->childrenRule;

        return $rule ? $rule->questions : \Illuminate\Database\Eloquent\Collection::make([]);
    }

    public function getQuestionModels()
    {
        $questions = $this->getChildrenModels();        

        return $questions;//$this->removeChildrenModels($questions);
    }

    public function sort($anchor)
    {
        $index = 0;
        $this->getParent()->getChildrenModels()->except($this->question->id)->sortBy('sorter')->each(function($question) use (&$index, $anchor) {
            $question->sorter = $index >= $anchor ? $index+1 : $index;
            $question->save();
            $index++;
        });

        $this->question->sorter = $anchor;

        $this->question->save();

        return $this;
    }

    public function setChildren($question)
    {
        $childrenRule = $this->question->childrenRule();

        $rule = $childrenRule->getResults() ?: $childrenRule->create([]);

        $rule->questions()->attach([$question->id]);
    }

    public function getModel()
    {
        return $this->question->load(['answers']);
    }

}