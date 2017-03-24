<?php

namespace Plat\Survey;

use Plat\Eloquent\Survey as SurveyORM;

class EditorRepository
{
    public function getNodes($root)
    {
        if ($root->childrenNodes->isEmpty()) {
            $type = get_class($root) == 'Plat\Eloquent\Survey\Book' ? 'page' : 'explain';
            $node = $root->childrenNodes()->save(new SurveyORM\Node(['type' => $type]));

            $root->load('childrenNodes');
        }

        $nodes = $root->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions.node', 'answers', 'byRules'])->each(function ($node) {
            $node->sortByPrevious(['questions', 'answers']);
        });

        return $nodes;
    }

    public function getQuestion($book_id)
    {
        $questions = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->load('rule')->reduce(function ($carry, $page) {
            $questions = $page->getQuestions();
            $questions[0]['page'] = $page;
            return array_merge($carry, $questions);
        }, []);

        return $questions;
    }

    public function createNode($root, array $node, $previous_id)
    {
        $node = $root->childrenNodes()->save(new SurveyORM\Node($node))->after($previous_id)->load(['questions', 'answers']);

        return $node;
    }

    public function createQuestion($node_id, $previous_id)
    {
        $question = SurveyORM\Node::find($node_id)->questions()->save(new SurveyORM\Question([]))->after($previous_id);

        return $question;
    }

    public function createAnswer($node_id, $previous_id)
    {
        $answer = SurveyORM\Node::find($node_id)->answers()->save(new SurveyORM\Answer([]))->after($previous_id);

        return $answer;
    }

    public function saveTitle($class, $id, $title)
    {
        $item = $class::find($id);

        $item->update(['title' => $title]);

        return $item;
    }

    public function removeNode($node_id)
    {
        $node = SurveyORM\Node::find($node_id);

        if ($node->next) {
            $previous_id = $node->previous ? $node->previous->id : NULL;
            $node->next->update(['previous_id' => $previous_id]);
        }

        return $node->deleteNode();
    }

    public function removeQuestion($question_id)
    {
        $question = SurveyORM\Question::find($question_id);

        $node = $question->node;

        if ($question->next) {
            $previous_id = $question->previous ? $question->previous->id : NULL;
            $question->next->update(['previous_id' => $previous_id]);
        }

        $question->childrenNodes->each(function ($subNode) {
            $subNode->deleteNode();
        });

        return [$question->delete(), $node->questions];
    }

    public function removeAnswer($answer_id)
    {
        $answer = SurveyORM\Answer::find($answer_id);

        $node = $answer->node;

        if ($answer->next) {
            $previous_id = $answer->previous ? $answer->previous->id : NULL;
            $answer->next->update(['previous_id' => $previous_id]);
        }

        $answer->childrenNodes->each(function($subNode) {
            $subNode->deleteNode();
        });

        return [$answer->delete(), $node->answers, $node];
    }

    public function updateAnswerValue($node)
    {
        $answersInNode = $node->sortByPrevious(['answers'])->answers;

        foreach ($answersInNode as $key => $answerInNode) {
            $answerInNode->update(['value' =>$key]);
        }

        return true;
    }
}