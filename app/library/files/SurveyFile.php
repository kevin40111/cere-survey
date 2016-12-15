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
        return ['open', 'demo', 'application'];
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

    public function application()
    {
        return 'files.survey.application-ng';
    }

    public function getBook()
    {
        return ['book' => $this->file->book];
    }

    public function getNodes()
    {
        $class = Input::get('root.class');

        $root = $class::find(Input::get('root.id'));

        if ($root->childrenNodes->isEmpty()) {
            $node = $root->childrenNodes()->save(new SurveyORM\Node(['type' => 'explain']));

            $root->load('childrenNodes');
        }

        $nodes = $root->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions', 'answers', 'byRules'])->each(function($node) {
            $node->sortByPrevious(['questions', 'answers']);
        });

        return ['nodes' => $nodes, 'paths' => $root->getPaths()];
    }

    public function createTable()
    {
        \Schema::create($this->file->book->id, function ($table) {
            $table->increments('id');
            $questions = $this->file->book->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function($carry, $page) {
                $questions = $page->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function($carry, $node) {
                    return array_merge($carry, $node->sortByPrevious(['questions'])->questions->toArray());
                }, []);
                return array_merge($carry, $questions);
            }, []);

            foreach ($questions as $question) {
                $table->text($question['id'])->nullable();
            }

            $table->integer('page_id');
            $table->integer('created_by');
        });

        return 1;
    }

    public function createNode()
    {
        $class = Input::get('parent.class');

        $parent = $class::find(Input::get('parent.id'));

        $node = $parent->childrenNodes()->save(new SurveyORM\Node(Input::get('node')))->after(Input::get('previous.id'));

        return ['node' => $node->load(['questions', 'answers']), 'next' => $node->next];
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
        $node = SurveyORM\Node::find(Input::get('node.id'));

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
        $node = SurveyORM\Node::find(Input::get('node.id'));

        return ['deleted' => $node->delete()];
    }

    public function removeQuestion()
    {
        $question = SurveyORM\Question::find(Input::get('question.id'));

        return ['deleted' => $question->delete(), 'questions' => $question->node->questions];
    }

    public function removeAnswer()
    {
        $answer = SurveyORM\Answer::find(Input::get('answer.id'));

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

        $item = $class::find(Input::get('item.id'))->moveUp();

        return ['item' => $item->load(['questions', 'answers']), 'previous' => $item->previous->load(['questions', 'answers'])];
    }

    public function moveNodeDown()
    {
        $class = '\\' . Input::get('item.class');

        $relation = Input::get('item.relation');

        $item = $class::find(Input::get('item.id'))->moveDown();

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

    public function setApplication()
    {
        return User::find(192)->extQuestions()->get();
    }

    public function getApplication()
    {
        $items = [
            'columns' => [],
            'options' => [],
        ];
        return $this->file->book->extention()->get();
        // return ['items' => $items,'rawdata' => User::find(192)->extQuestions()->first()];
    }

}