<?php

namespace Cere\Survey;

use Cere\Survey\Eloquent\Field\Field as Question;
use Illuminate\Database\Eloquent\Collection;
use Cere\Survey\Eloquent\Node;;

trait Tree
{
    public function getPaths()
    {
        $parent = is_a($this, 'Cere\Survey\Eloquent\Answer') || is_a($this, Question::class) ? $this->node->parent : $this->parent;

        $paths = $parent ? $parent->getPaths()->add($this) : \Illuminate\Database\Eloquent\Collection::make([$this]);

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

        return $questions->load(['node.answers.rule', 'rule', 'node.rule']);
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
