<?php

namespace Cere\Survey;

use Plat\Files\Uploader;
use Cere\Survey\Eloquent as SurveyORM;
use Cere\Survey\Eloquent\Field\Field as Question;

class Editor
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

        $nodes = $root->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions.rule', 'questions.noneAboveRule', 'rule', 'limitRule', 'answers.rule', 'images'])->each(function ($node) {
            $node->sortByPrevious(['questions', 'answers']);
        });

        return $nodes;
    }

    public function getQuestion($book_id)
    {
        $questions = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->load('rule')->reduce(function ($carry, $page) {
            $questions = $page->getQuestions();

            foreach ($questions as &$question) {
                $question['node']['title'] = strip_tags($question['node']['title']);
            }

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

        $title = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $title);

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
        $answers = \Excel::load($file, function ($reader) {
            $reader->noHeading();
        })->get();

        $heads = $answers->pull(0);

        if (! $answers->isEmpty()) {

            $node = SurveyORM\Node::find($node_id);

            $node->questions->each(function ($question) {
                $this->removeQuestion($question->id);
            });

            $node->answers->each(function ($answer) {
                $this->removeAnswer($answer->id);
            });

            $nodes = $this->createGearQuestion($heads, [$node]);
            $this->createGearAnswer($nodes, $answers->reduce(function ($carry, $answer) {
                $keys = implode('.', $answer->toArray());
                array_set($carry, $keys, []);
                return $carry;
            }, []));
        }

        return $node->load(['questions', 'answers']);
    }

    private function createGearQuestion($heads, $nodes)
    {
        foreach ($heads as $index => $head) {
            $question = $this->createQuestion(end($nodes)->id, null);
            $question->update(['title' => $head]);
            if ($index < sizeof($heads)-1) {
                array_push($nodes, $this->createNode($question, ['type' => 'gear'], null));
            };
        }

        return $nodes;
    }

    private function createGearAnswer($nodes, $categories, $category_id = null)
    {
        $answer_id = null ;
        $node = array_shift($nodes);

        foreach ($categories as $answer => $subCategories) {
            $answer_id = $node->answers()->create(['title' => $answer, 'value' => $answer, 'previous_id' => $answer_id, 'category_id' => $category_id])->id;
            if (! empty($nodes)) {
                $this->createGearAnswer($nodes, $subCategories, $answer_id);
            }
        }
    }

    public function removeBanner($node_id, $image_id)
    {
        return SurveyORM\Node::find($node_id)->images()->detach($image_id);
    }

    public function uploaderBanner($image_file, $forder_id, $node_id)
    {
        $upload = new Uploader($image_file, ['jpg', 'png'], 10000000);
        $result = $upload->fileUpload($forder_id);
        if($result['message'] == '檔案上傳成功') {
            SurveyORM\Node::find($node_id)->images()->attach($result['file']->id);
        }

        return[
            'message' => $result['message'],
            'images' => SurveyORM\Node::find($node_id)->images()->get(),
        ];
    }
}
