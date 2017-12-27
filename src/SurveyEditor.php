<?php

namespace Cere\Survey;

use DB;
use Schema;
use Input;
use View;
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

    public function getBook()
    {
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

    public function setNoPopulationColumn()
    {
        return Survey\ApplicationRepository::book($this->book)->setNoPopulationColumn(Input::get('column'));
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
}
