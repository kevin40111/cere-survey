<?php

namespace Cere\Survey;

use DB;
use Schema;
use Input;
use View;
use Auth;
use Cere\Survey;
use Cere\Survey\Eloquent as SurveyORM;
use Cere\Survey\Eloquent\Field\Field;


trait SurveyEditor
{
    function __construct($field)
    {
        $this->editorRepository = new Survey\EditorRepository($field);
    }

    public function open()
    {
        return 'survey::editor-ng';
    }

    public function demo()
    {
        return 'survey::demo-ng';
    }

    public function browser()
    {
        return 'survey::browser-ng';
    }

    public function surveyTime()
    {
        return 'survey::surveyTime-ng';
    }

    public function questionBrowser()
    {
        return  View::make('survey::template_question_browser');
    }

    public function loginCondition()
    {
        return 'survey::extend.loginCondition-ng';
    }

    public function getBook()
    {
        $this->book->loginColumn = Field::find($this->book->loginRow_id);

        if ($this->book->no_population) {
            $this->book->rowsFile = $this->book->noRows;
        } else {
            $this->book->rowsFile = $this->book->file;
        }

        return ['book' => $this->book];
    }

    public function getQuestion()
    {
        $questions = $this->editorRepository->getQuestion(Input::get('book_id'));

        return ['questions' => $questions];
    }

    public function getAnswers()
    {
        $answers = Field::find(Input::get('question_id'))->node->answers;

        return ['answers' => $answers];
    }

    public function getNodes()
    {
        $class = Input::get('root.class');

        $root = $class::find(Input::get('root.id'));

        $nodes = $this->editorRepository->getNodes($root);

        return ['nodes' => $nodes, 'paths' => $root->getPaths()];
    }

    public function createNode()
    {
        $class = Input::get('parent.class');

        $parent = $class::find(Input::get('parent.id'));

        $node = $this->editorRepository->createNode($parent, Input::get('node'), Input::get('previous.id'));

        return ['node' => $node, 'next' => $node->next];
    }

    public function createQuestion()
    {
        $question = $this->editorRepository->createQuestion(Input::get('node.id'), Input::get('previous.id'));

        return ['question' => $question];
    }

    public function createAnswer()
    {
        $answer = $this->editorRepository->createAnswer(Input::get('node.id'), Input::get('previous.id'));

        return ['answer' => $answer];
    }

    public function saveNodeTitle()
    {
        $node = $this->editorRepository->saveTitle(Input::get('node.class'), Input::get('node.id'), Input::get('node.title'));

        return ['node' => $node];
    }

    public function saveQuestionTitle()
    {
        $question = $this->editorRepository->saveTitle(Input::get('question.class'), Input::get('question.id'), Input::get('question.title'));

        return ['question' => $question];
    }

    public function saveAnswerTitle()
    {
        $answer = $this->editorRepository->saveTitle(Input::get('answer.class'), Input::get('answer.id'), Input::get('answer.title'));

        $this->editorRepository->updateAnswerValue($answer->node);

        return ['answer' => $answer];
    }

    public function removeNode()
    {
        $deleted = $this->editorRepository->removeNode(Input::get('node.id'));

        return ['deleted' => $deleted];
    }

    public function removeQuestion()
    {
        list ($deleted, $questions) = $this->editorRepository->removeQuestion(Input::get('question')['id']);

        return ['deleted' => $deleted, 'questions' => $questions];
    }

    public function removeAnswer()
    {
        list ($deleted, $answers, $node) = $this->editorRepository->removeAnswer(Input::get('answer.id'));

        $this->editorRepository->updateAnswerValue($node);

        return ['deleted' => $deleted, 'answers' => $answers];
    }

    public function moveUp()
    {
        $class = '\\' . Input::get('item.class');

        $relation = Input::get('item.relation');

        $item = $class::find(Input::get('item.id'))->moveUp();

        if ($class == '\\Plat\Eloquent\Survey\Answer') {
            $this->editorRepository->updateAnswerValue($item->node);
        }

        return ['items' => $item->node->sortByPrevious([$relation])->$relation];
    }

    public function moveDown()
    {
        $class = '\\' . Input::get('item.class');

        $relation = Input::get('item.relation');

        $item = $class::find(Input::get('item.id'))->moveDown();
        if ($class == '\\Plat\Eloquent\Survey\Answer') {
            $this->editorRepository->updateAnswerValue($item->node);
        }

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

    public function saveRule()
    {
        $class = Input::get('skipTarget.class');
        $root = $class::find(Input::get('skipTarget.id'));
        $rule = Survey\RuleRepository::target($root)->saveExpressions(Input::get('expressions'), Input::get('type'));

        return 'save rules successed';
    }

    public function deleteRule()
    {
        $class = Input::get('skipTarget.class');
        $root = $class::find(Input::get('skipTarget.id'));

        Survey\RuleRepository::target($root)->deleteRule();

        return 'delete rules successed';
    }

    public function getRule()
    {
        $class = Input::get('skipTarget.class');
        $root = $class::find(Input::get('skipTarget.id'));

        $rule = Survey\RuleRepository::target($root)->getRule();

        return ['rule' => $rule];
    }

    public function lockBook()
    {
        $this->book->update(['lock' => true]);

        return ['lock' => true];
    }

    public function getExpressionExplanation()
    {
        $explanation = Survey\RuleRepository::find(Input::get('rule_id'))->explanation();

        return ['explanation' => $explanation];
    }

    public function getTime()
    {
        return ['start_at' => $this->file->book->start_at, 'close_at' => $this->file->book->close_at];
    }

    public function setTime()
    {
        return (int)$this->file->book->update(['start_at' => Input::get('start_at'), 'close_at' => Input::get('close_at')]);
    }

    public function saveGearQuestion()
    {
        $gear_file = Input::file('file_upload');
        $node_id = Input::get('node_id');
        return $this->editorRepository->saveGearQuestion($gear_file, $node_id);
    }

    public function setNoPopulationColumn()
    {
        $column = Input::get('column');
        $input = ['name' => $column['name'], 'title' => $column['title'], 'rules' => $column['rule'], 'unique'  => true, 'encrypt' => false, 'isnull'  => false, 'readonly'=> false];
        if (is_null($this->book->no_pop_id)) {
            $file = new \Files(['type' => 5, 'title' => $this->book->title.'-無母體']);
            $rows_file = new \Plat\Files\RowsFile($file,Auth::user());
            $rows_file->create();
            $this->book->update(['no_pop_id' => $file->id, 'no_population' => true]);
            $table = $file->sheets->first()->tables->first();
            $column = $table->columns()->create($input);
            return $column;
        } else {
            $column = \Files::find($this->book->no_pop_id)->sheets->first()->tables->first()->columns->first();
            $column->update($input);
            return $column;
        }
    }

    public function getColumns()
    {
        $book = Input::get('book');

        $rowsFileId = $book['rowsFile_id'];
        $noPopulation = $book['no_population'];

        $noColumns = $this->book->optionColumns->isEmpty();

        if($noColumns) {
            $file = $noPopulation ? \Files::find($this->book->no_pop_id) : \Files::find($rowsFileId);
            $columns = !is_null($file) ? $file->sheets->first()->tables->first()->columns : [];
        } else {
            $columns = $this->book->optionColumns;
        }

        return ['columns' => $columns];
    }

    public function getRowsTable()
    {
        $no_population = \Files::all()->filter(function ($file) {
            return ($file->created_by ==  Auth::user()->id) ? (is_null($file->book) ? false : $file->book->no_pop_id) : false;
        })->map(function($file) {
            return $file->book->no_pop_id;
        })->toArray();
        $rowsTables = $this->book->file->select('id', 'title')->where('created_by', '=', Auth::user()->id)->where('type','=','30')->whereNotIn('id', $no_population)->get();

        $rowRules = (new \Plat\Files\RowsFile(\Files::first(),Auth::user()))->rules;

        return ['rowsTables' => $rowsTables, 'rowRules' => $rowRules];
    }

    public function setLoginCondition()
    {
        $book = Input::get('book');
        $lock = Input::get('lock');

        if($lock) {
            $this->book->update([
                'loginRow_id' => $book['loginRow_id'],
                'rowsFile_id' => $book['rowsFile_id'],
                'no_population' => $book['no_population'],
                'lock' => $lock
            ]);
        } else {
            $this->book->applications->each(function($application){
                $application->delete();
            });
            $this->book->applicableOptions()->delete();

            $book = $this->book;
            $book->column_id = NULL;
            $book->rowsFile_id = NULL;
            $book->no_population = 0;
            $book->lock = false;
            $book->loginRow_id = NULL;
            $book->save();
        }
    }
}
