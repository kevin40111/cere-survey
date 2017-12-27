<?php

namespace Cere\Survey;

use Cere\Survey\Eloquent as SurveyORM;
use Cere\Survey\Eloquent\Field\Field as Question;

class EditorRepository
{
    function __construct($filed)
    {
        $this->filed = $filed;
    }

    public function create()
    {
        $this->filed->update_column(null, ['name' => 'page_id', 'rules' => 'gender']);
        $this->filed->update_column(null, ['name' => 'encrypt_id', 'rules' => 'gender']);
    }

    public function getNodes($root)
    {
        if ($root->childrenNodes->isEmpty()) {
            $type = get_class($root) == 'Cere\Survey\Eloquent\Book' ? 'page' : 'explain';
            $node = $root->childrenNodes()->save(new SurveyORM\Node(['type' => $type]));

            $root->load('childrenNodes');
        }

        $nodes = $root->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions.node', 'answers'])->each(function ($node) {
            $node->sortByPrevious(['questions', 'answers']);
        });

        return $nodes;
    }

    public function getQuestion($book_id)
    {
        $questions = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->load('rule')->reduce(function ($carry, $page) {
            $questions = $page->getQuestions();
            if (count($questions) > 0) {
                $questions[0]['page'] = $page;
            }
            return array_merge($carry, $questions);
        }, []);

        return $questions;
    }

    public function createNode($root, array $node, $previous_id)
    {
        $node = $root->childrenNodes()->save(new SurveyORM\Node($node))->after($previous_id);

        if ($node->type != 'explain' && $node->type != 'page' && $node->type != 'gear' ) {

            $this->createQuestion($node->id, null);
        }

        return $node->load(['questions', 'answers']);
    }

    public function createQuestion($node_id, $previous_id)
    {
        $column = $this->filed->update_column(null, ['rules' => 'gender']);

        $question = SurveyORM\Node::find($node_id)->questions()->save($column)->after($previous_id);

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

        strlen($title) <= 2000 && $item->update(['title' => $title]);

        return $item;
    }

    public function removeNode($node_id)
    {

        $node = SurveyORM\Node::find($node_id);

        $questions = $this->getQuestions($node_id);

        if ($node->next) {
            $previous_id = $node->previous ? $node->previous->id : NULL;
            $node->next->update(['previous_id' => $previous_id]);
        }

        foreach ($questions as $question) {
            $this->filed->remove_column($question['id']);
        }

        return $node->deleteNode();
    }

    private function getQuestions($node_id)
    {
        $node = SurveyORM\Node::find($node_id);

        switch ($node->type) {
            case 'page':
                return $node->getQuestions();
            break;

            default:
                return $node->questions->reduce(function ($carry, $question) {
                    return array_merge($carry, $question->getQuestions(), [$question->toArray()]);
                }, []);
            break;
        }
    }

    public function removeQuestion($question_id)
    {
        $question = Question::find($question_id);

        $node = $question->node;

        if ($question->next) {
            $previous_id = $question->previous ? $question->previous->id : NULL;
            $question->next->update(['previous_id' => $previous_id]);
        }

        $question->childrenNodes->each(function ($subNode) {
            $subNode->deleteNode();
        });

        $this->filed->remove_column($question_id);

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

    public function saveGearQuestion($file, $node_id)
    {
        $answers_tree = [];
        $deep;

        \Excel::load($file, function($reader) use (&$answers_tree, &$deep){
            /*noHeading is read the row from first, not second*/
            $reader->noHeading();
            $reader = $reader->toArray();

            $deep = sizeof(reset($reader));

            foreach ($reader as $row) {
                $keys = implode('.', array_slice($row, 0, $deep));
                $value = $row[$deep-1];
                array_set($answers_tree, $keys, $value);
            }
        });

        if (!empty($answers_tree)) {

            $node = SurveyORM\Node::find($node_id)->load(['questions', 'answers']);

            foreach ($node->questions as $question) {
                $this->removeQuestion($question->id);
            }

            foreach ($node->answers as $answer) {
                $this->removeAnswer($answer->id);
            }

            $this->createGearQuestion($deep, $node_id, $answers_tree);
            $this->createGearAnswer($answers_tree, 0);
        }

        return SurveyORM\Node::find($node_id)->load(['questions', 'answers']);
    }

    private function createGearQuestion($deep, $node_id, $answers_tree)
    {
        $last_node_id = $node_id;
        $node_array = [];
        array_push($node_array, $last_node_id);

        for ($i=0; $i < $deep; $i++) {
            $question = $this->createQuestion($last_node_id, null);
            if($i < $deep-1) {
                $last_node_id = $this->createNode($question, ['type' => 'gear'], null)->id;
                array_push($node_array,  $last_node_id);
            };
        }

        $this->node_array = $node_array;

    }

    private function createGearAnswer($answer_array, $node_level, $belong = null)
    {
        $last_answer_id = null ;
        foreach ($answer_array as $key => $value) {
            $answer = SurveyORM\Answer::create(['title' => $key, 'value' => $key, 'node_id' => $this->node_array[$node_level], 'previous_id' => $last_answer_id, 'belong' => $belong]);
            if (gettype($value) == "array") {
                $this->createGearAnswer($value, $node_level+1, $answer->id);
            }
            $last_answer_id = $answer->id;
        }
    }
}
