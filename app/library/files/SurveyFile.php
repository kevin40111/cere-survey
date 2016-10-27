<?php
namespace Plat\Files;

use DB;
use Input;
use User;
use Files;
use Plat\Survey;
use Plat\Eloquent\Survey as SurveyORM;

class SurveyFile extends CommFile {

    function __construct(Files $file, User $user)
    {
        parent::__construct($file, $user);
    }

    public function is_full()
    {
        return false;
    }

    public function get_views()
    {
        return ['open', 'demo'];
    }

    public function create()
    {
        $commFile = parent::create();

        $book = $this->file->book()->create(['title' => $this->file->title]);
    }

    public function open()
    {
        return 'files.survey.editor-ng';
    }

    public function demo()
    {
        return 'files.survey.demo-ng';
    }

    public function getBook()
    {
        return ['book' => $this->file->book, 'edit' => true, 'log' => DB::getQueryLog()];
    }

    public function getNodes()
    {
        $class = Input::get('parent.class');

        $parent = $class::find(Input::get('parent.id'));

        $nodes = $parent->initNode()->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions', 'answers', 'byRules'])->each(function($node) {
            $node->sortByPrevious(['questions', 'answers']);
        });

        return ['nodes' => $nodes, 'paths' => $parent->getPaths()];
    }

    public function getNextNode()
    {
        if (Input::has('node.id')) {
            $childrens = Survey\Node::find(Input::get('node.id'))->childrenModels();

            $node = $childrens->isEmpty() ? Survey\Node::find(Input::get('node.id'))->getModel()->next : $childrens->first();
        } else {
            $node = $this->file->book->childrenNodes->first();
        }

        return ['node' => $node->load(['questions', 'answers'])];
    }

    public function createNode()
    {
        $class = Input::get('parent.class');

        $parent = $class::find(Input::get('parent.id'));

        $node = Survey\Node::create($parent, Input::get('node'))->getModel()->after(Input::get('previous.id'));

        return ['node' => $node];
    }

    public function createQuestion()
    {
        $question = SurveyORM\Node::find(Input::get('node.id'))->questions()->save(new SurveyORM\Question([]))->after(Input::get('previous.id'));

        return ['question' => $question];
    }

    public function createAnswer()
    {
        $answer = SurveyORM\Node::find(Input::get('node.id'))->answers()->save(new SurveyORM\Answer([]))->after(Input::get('previous.id'));

        return ['answer' => $answer];
    }

    public function saveNodeTitle()
    {
        $node = Survey\Node::find(Input::get('node.id'))->getModel();

        $node->update(['title' => Input::get('node.title')]);

        return ['title' => $node->title];
    }

    public function saveQuestionTitle()
    {
        $question = SurveyORM\Question::find(Input::get('question.id'));

        $question->update(['title' => Input::get('question.title')]);

        return ['question' => $question];
    }

    public function saveAnswerTitle()
    {
        $answer = SurveyORM\Answer::find(Input::get('answer.id'));

        $answer->update(['title' => Input::get('answer.title')]);

        return ['answer' => $answer];
    }

    public function removeNode()
    {
        $node = Survey\Node::find(Input::get('node.id'));

        $node->getModel()->next->after($node->getModel()->previous->id);

        return ['deleted' => $node->delete()];
    }

    public function removeQuestion()
    {
        $question = SurveyORM\Question::find(Input::get('question.id'));

        $question->next->after($question->previous->id);

        return ['deleted' => $question->delete(), 'questions' => $question->node->questions];
    }

    public function removeAnswer()
    {
        $answer = SurveyORM\Answer::find(Input::get('answer.id'));

        $answer->next->after($answer->previous->id);

        return ['deleted' => $answer->delete(), 'answers' => $answer->node->answers];
    }

    public function moveUp()
    {
        $class = '\\' . Input::get('item.class');

        $relation = Input::get('item.relation');

        $item = $class::find(Input::get('item.id'))->moveUp();

        return ['items' => $item->node->sortByPrevious([$relation])->$relation];
    }

    public function moveDown()
    {
        $class = '\\' . Input::get('item.class');

        $relation = Input::get('item.relation');

        $item = $class::find(Input::get('item.id'))->moveDown();

        return ['items' => $item->node->sortByPrevious([$relation])->$relation];
    }

    public function moveNodeUp()
    {
        $class = '\\' . Input::get('item.class');

        $relation = Input::get('item.relation');

        $item = $class::find(Input::get('item.id'))->moveUp()->getModel();

        return ['item' => $item->load(['questions', 'answers']), 'previous' => $item->previous->load(['questions', 'answers'])];
    }

    public function moveNodeDown()
    {
        $class = '\\' . Input::get('item.class');

        $relation = Input::get('item.relation');

        $item = $class::find(Input::get('item.id'))->moveDown()->getModel();

        return ['item' => $item->load(['questions', 'answers']), 'next' => $item->next->load(['questions', 'answers'])];
    }

    public function getBooks()
    {
        return ['books' => Set\Book::all()];
    }

    public function getColumns()
    {
        $columns = \ShareFile::find(Input::get('file_id'))->isFile->sheets->first()->tables->first()->columns;

        return ['columns' => $columns];
    }

}