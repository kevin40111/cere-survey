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

        $node = Survey\Node::create($parent, Input::get('node'));

        return ['node' => $node->getModel()];
    }

    public function createQuestion()
    {
        $question = SurveyORM\Node::find(Input::get('node.id'))->questions()->save(new SurveyORM\Question([]))->after(Input::get('previous.id'));

        return ['question' => $question];
    }

    public function createAnswer()
    {
        $answer = SurveyORM\Node::find(Input::get('node.id'))->answers()->save(new SurveyORM\Answer([]));

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

        return ['deleted' => $answer->delete(), 'answers' => $answer->node->answers];
    }

    public function moveSort()
    {
        $questions = Survey\Question::find(Input::get('question.id'))->sort(Input::get('question.sorter')*1)->getParent()->getChildrenModels()->sortBy('sorter');

        return ['questions' => $questions->load(['answers'])->values()];
    }

    public function moveChildrenSort()
    {
        $sbook = $this->file->book->set;

        $cQuestions = $sbook->questions()->page(Input::get('question.page'))->where('parent_answer_id', Input::get('question.parent_answer_id'))->get()->except(Input::get('question.id'));

        $this->sortQuestion(Input::get('question.sorter')*1, $cQuestions);

        $cQuestion = Set\Question::find(Input::get('question.id'));

        $cQuestion->sorter = Input::get('question.sorter');

        $cQuestion->save();

        return ['question' => $cQuestion->load(['answers', 'parent'])];
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