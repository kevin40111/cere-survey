<?php

namespace Plat\Survey;

use Input;
use Plat\Eloquent\Survey as SurveyORM;

class Book {

    use Children;

    protected $book;

    protected $page;

    function __construct(SurveyORM\Book $book)
    {
        $this->book = $book;
    }

    public static function make(SurveyORM\Book $book)
    {
        return new static($book);
    }

    public static function find($id)
    {
        $book = SurveyORM\Book::find($id);

        return new static($book);
    }

    public function getModel()
    {
        return $this->book;
    }

    public function getChildrenNodeModels()
    {
        return $this->book->childrenNodes;
    }

    public function getPaths()
    {
        return [$this->book];
    }

    public function getFirstNode()
    {        
        return Node::make($this->book->childrenNodes()->whereNull('previous_id')->first());
    }

    public function removeChildrenModels($questions)
    {
        return $questions->filter(function($question) {
            return $question->byRules->isEmpty();
        });
    }

}