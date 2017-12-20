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

    public function application()
    {
        return 'survey::application-ng';
    }

    public function confirm()
    {
        return 'survey::confirm-ng';
    }

    public function applicableList()
    {
        return 'survey::applicableList-ng';
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

    public function userApplication()
    {
        return View::make('survey::userApplication-ng');
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

    public function setAppliedOptions()
    {
        $selected = Input::get('selected');

        return Survey\ApplicationRepository::book($this->book)->setAppliedOptions($selected);
    }

    public function getAppliedOptions()
    {
        $member_id = Input::get('member_id');

        return Survey\ApplicationRepository::book($this->book)->getAppliedOptions($member_id);
    }

    public function resetApplication()
    {
        return Survey\ApplicationRepository::book($this->book)->resetApplication();
    }

    public function setApplicableOptions()
    {
        Survey\ApplicationRepository::book($this->book)->setApplicableOptions(Input::get('selected'), Input::get('noPopulation'));
        return $this->getApplicableOptions();
    }

    public function getApplicableOptions()
    {
        return Survey\ApplicationRepository::book($this->book)->getApplicableOptions(Input::get('rowsFileId'), Input::get('noPopulation'));
    }

    public function getApplications()
    {
        $applications = $this->book->applications->load('members.organizations.now', 'members.user', 'members.contact');

        return ['applications' => $applications];
    }

    public function resetApplicableOptions()
    {
        Survey\ApplicationRepository::book($this->book)->resetApplicableOptions();

        return $this->getApplicableOptions();
    }

    public function activeExtension()
    {
        $application_id = Input::get('application_id');
        $application = $this->book->applications()->where('id', $application_id)->first();
        if (!$application->reject) {
            SurveyORM\Book::find($application->ext_book_id)->update(array('lock' => true));
        }
        $application->extension = !$application->extension;
        $application->save();

        return ['application' => $application];
    }

    public function reject()
    {
        $application = $this->book->applications()->where('id', Input::get('application_id'))->first();

        $application = Survey\ApplicationRepository::application($application)->reject();

        return ['application' => $application];
    }

    public function getApplicationPages()
    {
        $pagination = Survey\ApplicationRepository::book($this->book)->getApplicationPages();

        return ['currentPage' => $pagination->getCurrentPage(), 'lastPage' => $pagination->getLastPage()];
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

    public function checkExtBookLocked()
    {
        $locked = SurveyORM\Book::find(Input::get('book_id'))->lock;

        return  ['ext_locked' => $locked];
    }

    public function getExpressionExplanation()
    {
        $explanation = Survey\RuleRepository::find(Input::get('rule_id'))->explanation();

        return ['explanation' => $explanation];
    }

    public function applicationStatus()
    {
        $application = $this->book->applications()->OfMe()->first();
        if (is_null($application)) {
            return ['status' => null];
        } else {
            if ($application->extension ==  $application->reject) {
                $status = '0';
            } else if ($application->reject) {
                $status = '1';
            } else {
                $status = '2';
            }
            return ['status' => $status];
        }
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
}