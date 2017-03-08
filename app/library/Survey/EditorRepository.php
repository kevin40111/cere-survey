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

        $nodes = $root->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions.node', 'answers', 'byRules'])->each(function($node) {
            $node->sortByPrevious(['questions', 'answers']);
        });

        return $nodes;
    }

    public function getQuestion($book_id)
    {
        $questions = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) {
            return array_merge($carry, $page->getQuestions());
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

    public function updateAnswerValue($node)
    {
        $answersInNode = $node->sortByPrevious(['answers'])->answers;

        foreach ($answersInNode as $key => $answerInNode) {
            $answerInNode->update(['value' =>$key]);
        }

        return true;
    }
}