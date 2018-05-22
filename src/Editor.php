<?php

namespace Cere\Survey;

use Plat\Files\Uploader;
use Cere\Survey\Eloquent as SurveyORM;
use Cere\Survey\Eloquent\Question;

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
        $nodes = $root->childrenNodes->load(['questions', 'answers', 'skipers', 'guarders', 'images']);

        return $nodes;
    }

    public function getPages($book_id)
    {
        $pages = SurveyORM\Book::find($book_id)->childrenNodes->load('skiper')->map(function ($page, $index) {
            $questions = $page->getQuestions()->load('node.answers')->each(function ($question) {
                $question->node->title = strip_tags($question->node->title);
            });
            return ['title' => $index+1, 'skiper' => $page->skiper, 'questions' => $questions];
        });

        return $pages;
    }

    public function createNode($root, array $attributes)
    {
        $node = $root->childrenNodes()->save(new SurveyORM\Node($attributes));

        if ($node->type != 'explain' && $node->type != 'page' && $node->type != 'gear' ) {

            $this->createQuestion($node->id, ['position' => 0]);
        }

        return $node->load(['questions', 'answers']);
    }

    public function createQuestion($node_id, $attributes)
    {
        $column = $this->filed->update_column(null, ['rules' => 'gender']);

        $question = new Question($attributes);

        $question->node()->associate(SurveyORM\Node::find($node_id));

        $question->field()->associate($column);

        $question->save();

        return $question;
    }

    public function createAnswer($node_id, array $attributes)
    {
        $answer = SurveyORM\Node::find($node_id)->answers()->save(new SurveyORM\Answer($attributes));

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

        $node->getQuestions()->each(function ($question) {
            $this->filed->remove_column($question->field->id);
        });

        return $node->deleteNode();
    }

    public function removeQuestion($question_id)
    {
        $question = Question::find($question_id);

        $node = $question->node;

        $question->childrenNodes->each(function ($subNode) {
            $subNode->deleteNode();
        });

        $this->filed->remove_column($question->field->id);

        return $question->delete();
    }

    public function removeAnswer($answer_id)
    {
        $answer = SurveyORM\Answer::find($answer_id);

        $node = $answer->node;

        $answer->childrenNodes->each(function($subNode) {
            $subNode->deleteNode();
        });

        return $answer->delete();
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
