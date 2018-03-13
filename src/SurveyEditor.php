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
use Cere\Survey\Field\FieldComponent;
use Cere\Survey\Field\FieldRepository;
use Files;


trait SurveyEditor
{
    function __construct($field)
    {
        $this->editor = new Survey\Editor($field);
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
        return 'survey::auth.surveyTime-ng';
    }

    public function questionBrowser()
    {
        return  View::make('survey::template_question_browser');
    }

    public function loginCondition()
    {
        return 'survey::auth.loginCondition-ng';
    }

    public function getBook()
    {
        return ['book' => $this->book];
    }

    public function getQuestion()
    {
        $questions = $this->editor->getQuestion(Input::get('book_id'));

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

        $nodes = $this->editor->getNodes($root);

        if ($root->node) {
            $parent = [];
            if ($root->node->type == 'checkbox') {
                $parent['title'] = $root->node->title;
                $parent['items'] = $root->node->questions;
            } else {
                $parent['title'] = $root->node->questions->first()->title;
                $parent['items'] = $root->node->answers;
            }
            $parent['items']->find($root->id)->selected = true;
        }

        return ['nodes' => $nodes, 'paths' => $root->getPaths(), 'parent' => $root->node ? $parent : null];
    }

    public function createNode()
    {
        $class = Input::get('parent.class');

        $parent = $class::find(Input::get('parent.id'));

        $node = $this->editor->createNode($parent, Input::get('node'), Input::get('previous.id'));

        return ['node' => $node, 'next' => $node->next];
    }

    public function createQuestion()
    {
        $question = $this->editor->createQuestion(Input::get('node.id'), Input::get('previous.id'));

        return ['question' => $question];
    }

    public function createAnswer()
    {
        $answer = $this->editor->createAnswer(Input::get('node.id'), Input::get('previous.id'));

        return ['answer' => $answer];
    }

    public function saveNodeTitle()
    {
        $node = $this->editor->saveTitle(Input::get('node.class'), Input::get('node.id'), Input::get('node.title'));

        return ['node' => $node];
    }

    public function saveQuestionTitle()
    {
        $question = $this->editor->saveTitle(Input::get('question.class'), Input::get('question.id'), Input::get('question.title'));

        return ['question' => $question];
    }

    public function saveAnswerTitle()
    {
        $answer = $this->editor->saveTitle(Input::get('answer.class'), Input::get('answer.id'), Input::get('answer.title'));

        $this->editor->updateAnswerValue($answer->node);

        return ['answer' => $answer];
    }

    public function removeNode()
    {
        $deleted = $this->editor->removeNode(Input::get('node.id'));

        return ['deleted' => $deleted];
    }

    public function removeQuestion()
    {
        list ($deleted, $questions) = $this->editor->removeQuestion(Input::get('question')['id']);

        return ['deleted' => $deleted, 'questions' => $questions];
    }

    public function removeAnswer()
    {
        list ($deleted, $answers, $node) = $this->editor->removeAnswer(Input::get('answer.id'));

        $this->editor->updateAnswerValue($node);

        return ['deleted' => $deleted, 'answers' => $answers];
    }

    public function moveUp()
    {
        $class = '\\' . Input::get('item.class');

        $relation = Input::get('item.relation');

        $item = $class::find(Input::get('item.id'))->moveUp();

        if ($class == '\\Plat\Eloquent\Survey\Answer') {
            $this->editor->updateAnswerValue($item->node);
        }

        return ['items' => $item->node->sortByPrevious([$relation])->$relation];
    }

    public function moveDown()
    {
        $class = '\\' . Input::get('item.class');

        $relation = Input::get('item.relation');

        $item = $class::find(Input::get('item.id'))->moveDown();
        if ($class == '\\Plat\Eloquent\Survey\Answer') {
            $this->editor->updateAnswerValue($item->node);
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

        $page = $root->getPaths()->filter(function($item) {
            return $item->type == 'page';
        })->first();

        $rule = Survey\RuleRepository::target($root)->saveExpressions(Input::get('expressions'), Input::get('type'), $page->id);


        return ['rule' => $rule];
    }

    public function deleteRule()
    {
        $class = Input::get('skipTarget.class');
        $root = $class::find(Input::get('skipTarget.id'));
        $rule_id = Input::get('rule_id');


        Survey\RuleRepository::target($root)->deleteRule($rule_id);

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

    public function saveGearQuestion()
    {
        $gear_file = Input::file('file_upload');
        $node_id = Input::get('node_id');
        return $this->editor->saveGearQuestion($gear_file, $node_id);
    }

    public function createAuthField()
    {
        $input = Input::only(['field.name', 'field.title', 'field.rules'])['field'];

        $file = new Files(['type' => 30, 'title' => Input::get('fileTitle', $this->book->title.'-無母體')]);
        $rows_file = new FieldComponent($file, Auth::user());
        $rows_file->create();

        FieldRepository::target($file->sheets->first()->tables->first(), $this->user->id)->update_column(NULL, $input);

        return ['file' => $file];
    }

    public function getAuthOptions()
    {
        $fieldFiles = Files::where('created_by', '=', Auth::user()->id)->where('type', '=', '30')->get();

        return [
            'fieldFiles' => $fieldFiles,
            'fieldFile_id' => $this->book->auth['fieldFile_id'],
            'start_at' => $this->book->auth['start_at']->isSameDay(\Carbon\Carbon::minValue()) ? NULL : $this->book->auth['start_at']->toDateTimeString(),
            'close_at' => $this->book->auth['close_at']->isSameDay(\Carbon\Carbon::maxValue()) ? NULL : $this->book->auth['close_at']->toDateTimeString(),
            'rules' => FieldRepository::$rules,
        ];
    }

    public function getAuthFields()
    {
        $authFields = Files::find(Input::get('fieldFile_id'))->sheets->first()->tables->first()->columns->each(function($field) {
            $field->isInput = in_array($field->id, $this->book->auth['inputFields']);
            $field->isValid = in_array($field->id, $this->book->auth['validFields']);
        });

        return ['authFields' => $authFields];
    }

    public function setLoginCondition()
    {
        $this->book->update([
            'auth' => Input::get('auth')
        ]);
    }

    public function removeBanner()
    {
        return $this->editor->removeBanner(Input::get('image')['node_id'], Input::get('image')['upload_id']);
    }

    public function uploaderBanner()
    {
        return $this->editor->uploaderBanner(Input::file('file_upload'), $this->file->id, Input::get('id'));
    }

    public function saveBookFooter()
    {
        $this->file->book->update(['footer' => Input::get('footer')]);
        return ['footer' => $this->file->book->footer];
    }
}
