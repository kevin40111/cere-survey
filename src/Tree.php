<?php

namespace Cere\Survey;

use Cere\Survey\Eloquent\Field\Field as Question;

trait Tree {
    public function getPaths()
    {
        $parent = is_a($this, 'Plat\Eloquent\Survey\Answer') || is_a($this, Question::class) ? $this->node->parent : $this->parent;

        $paths = $parent ? $parent->getPaths()->add($this) : \Illuminate\Database\Eloquent\Collection::make([$this]);

        return $paths;
    }

    public function getQuestions()
    {
        $nodes = $this->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function($carry, $node) {

            $questions = $node->sortByPrevious(['questions'])->questions->reduce(function($carry, $question) {
                return array_merge($carry, $question->getQuestions());
            }, $node->sortByPrevious(['questions'])->questions->load(['node.answers.rule', 'rule', 'node.rule'])->toArray());

            $questionsWithInAnswer = $node->sortByPrevious(['answers'])->answers->reduce(function($carry, $answer) {
                return array_merge($carry, $answer->getQuestions());
            }, $questions);

            return array_merge($carry, $questionsWithInAnswer);
        }, []);

        return $nodes;
    }

    public function sortByPrevious(array $relations)
    {
        foreach ($relations as $relation) {

            $nextNodes = $this->$relation->keyBy('previous_id');

            $last = $nextNodes->pull('', false);

            $nodes = \Illuminate\Database\Eloquent\Collection::make([]);

            while ($last) {

                $nodes->push($last);

                $last = $nextNodes->pull($last->id, false);

            }

            $this->setRelation($relation, $nodes);
        }

        return $this;
    }

    public function after($previous_id)
    {
        $next = isset($previous_id) ? self::find($previous_id)->next : false;
        if ($next) {
            $next->update(['previous_id' => $this->id]);
        }

        $this->update(['previous_id' => $previous_id]);

        return $this;
    }

    public function moveUp()
    {
        $top_id = $this->previous->previous ? $this->previous->previous->id : NULL;

        $this->next && $this->next->after($this->previous->id);

        $this->previous->after($this->id);

        $this->after($top_id);

        return $this;
    }

    public function moveDown()
    {
        $this->next->moveUp();

        return $this;
    }
    public function delete()
    {
        if ($this->next) {
            if ($this->previous == NULL) {
                $this->next->after(NULL);
            } else {
                $this->next->after($this->previous->id);
            }
        }
        return parent::delete();
    }

    public function deleteNode()
    {
        $this->sortByPrevious(['questions'])->questions->each(function($question) {
            $question->childrenNodes->each(function($subNode) {
                $subNode->deleteNode();
            });
        });

        $this->sortByPrevious(['answers'])->answers->each(function($answer) {
            $answer->childrenNodes->each(function($subNode) {
                $subNode->deleteNode();
            });
        });

        return $this->delete();
    }

}
