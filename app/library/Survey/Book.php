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

    public function getChildrenModels()
    {
        $questions = $this->book->questions()->page(Input::get('page', 1))->get()->keyBy('id');

        $this->insertChildrens($questions);

        return $questions;
    }

    public function removeChildrenModels($questions)
    {
        return $questions->filter(function($question) {
            return $question->byRules->isEmpty();
        });
    }

    public function setChildren()
    {

    }

}