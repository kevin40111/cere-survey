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

        $nextNodes = $parent->initNode()->getChildrenNodeModels()->load([
            'questions',
            'answers',
            //'byRules',
        ])->keyBy('previous_id');

        $nodes = $nextNodes->isEmpty() ? [] : [$nextNodes['']];

        $nextNodes->each(function($node) use ($nextNodes, &$nodes) {
            $previous_id = $nodes[count($nodes)-1]->id;
            if (isset($nextNodes[$previous_id]))
                array_push($nodes, $nextNodes[$previous_id]);
        });

        return ['nodes' => $nodes, 'paths' => $parent->getPaths()];
    }

    public function getNextNode()
    {

        if (Input::has('node.id')) {
            $childrens = Survey\Node::find(Input::get('node.id'))->childrenModels();

            $node = $childrens->isEmpty() ? Survey\Node::find(Input::get('node.id'))->next()->getModel() : $childrens->first();
        } else {
            $node = Survey\Book::make($this->file->book)->getFirstNode()->getModel();
        }

        return ['node' => $node];
    }

    public function createNode()
    {
        $class = Input::get('parent.class');

        $parent = $class::find(Input::get('parent.id'))->getModel();

        $node = Survey\Node::create($parent, Input::get('node'));

        return ['node' => $node->getModel()];
    }

    public function createQuestion()
    {
        $question = Survey\Question::create(SurveyORM\Node::find(Input::get('node.id')), []);

        return ['question' => $question->getModel()];
    }

    public function createAnswer()
    {
        $answer = Survey\Answer::create(SurveyORM\Node::find(Input::get('node.id')), []);

        return ['answer' => $answer->getModel()];
    }

    public function saveNodeTitle()
    {
        $node = Survey\Node::find(Input::get('node.id'))->update(['title' => Input::get('node.title')]);

        return ['title' => $node->getModel()->title];
    }

    public function saveQuestionTitle()
    {
        $question = Survey\Question::find(Input::get('question.id'))->update(['title' => Input::get('question.title')]);

        return ['title' => $question->getModel()->title];
    }

    public function saveAnswerTitle()
    {
        $answer = Survey\Answer::find(Input::get('answer.id'))->update(['title' => Input::get('answer.title')]);

        return ['title' => $answer->getModel()->title];
    }

    public function removeNode()
    {
        $node = Survey\Node::find(Input::get('node.id'));

        return ['deleted' => $node->delete()];
    }

    public function removeQuestion()
    {
        $question = Survey\Question::find(Input::get('question.id'));

        return ['deleted' => $question->delete(), 'questions' => $question->getNodeModel()->questions];
    }

    public function removeAnswer()
    {
        $answer = Survey\Answer::find(Input::get('answer.id'));

        return ['deleted' => $answer->delete(), 'answers' => $answer->getNodeModel()->answers];
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