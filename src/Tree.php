<?php

namespace Cere\Survey;

use Illuminate\Database\Eloquent\Collection;
use Cere\Survey\Eloquent\Node;
use Cere\Survey\Eloquent\Question;
use Cere\Survey\Eloquent\Answer;

trait Tree
{
    public function getPaths()
    {
        $parent = is_a($this, Answer::class) || is_a($this, Question::class) ? $this->node->parent : $this->parent;

        $paths = $parent ? $parent->getPaths()->add($this) : Collection::make([$this]);

        return $paths;
    }

    public function getQuestions()
    {
        $questions = $this->childrenNodes->reduce(function ($carry, $node) {
            return $carry->merge($node->getQuestions());
        }, new Collection);

        if (is_a($this, Node::class)) {
            $questions = $this->questions->reduce(function ($carry, $question) {
                return $carry->add($question)->merge($question->getQuestions());
            }, $questions);

            $questions = $this->answers->reduce(function ($carry, $answer) {
                return $carry->merge($answer->getQuestions());
            }, $questions);
        }

        return $questions;
    }

    public function deleteNode()
    {
        $this->questions->each(function($question) {
            $question->childrenNodes->each(function($subNode) {
                $subNode->deleteNode();
            });
        });

        $this->answers->each(function($answer) {
            $answer->childrenNodes->each(function($subNode) {
                $subNode->deleteNode();
            });
        });

        return $this->delete();
    }
}
