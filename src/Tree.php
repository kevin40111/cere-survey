<?php

namespace Cere\Survey;

use Cere\Survey\Eloquent\Field\Field as Question;

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
        $nodes = $this->childrenNodes->reduce(function($carry, $node) {

            $questions = $node->questions->reduce(function($carry, $question) {
                return array_merge($carry, $question->getQuestions());
            }, $node->questions->load(['node.answers.rule', 'rule', 'node.rule'])->toArray());

            $questionsWithInAnswer = $node->answers->reduce(function($carry, $answer) {
                return array_merge($carry, $answer->getQuestions());
            }, $questions);

            return array_merge($carry, $questionsWithInAnswer);
        }, []);

        return $nodes;
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
