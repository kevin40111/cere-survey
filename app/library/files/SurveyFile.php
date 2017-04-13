<?php

namespace Plat\Files;

use DB;
use Schema;
use Input;
use View;
use User;
use Files;
use Mail;
use Plat\Survey;
use Plat\Eloquent\Survey as SurveyORM;

class SurveyFile extends CommFile
{
    function __construct(Files $file, User $user)
    {
        parent::__construct($file, $user);

        $this->configs = $this->file->configs->lists('value', 'name');

        $this->editorRepository = new Survey\EditorRepository();
    }

    public function is_full()
    {
        return false;
    }

    public function get_views()
    {
        return ['open', 'demo', 'application','confirm', 'applicableList', 'browser'];
    }

    public static function tools()
    {
        return [
            ['name' => 'confirm', 'title' => '加掛審核', 'method' => 'confirm', 'icon' => 'list'],
            ['name' => 'applicableList', 'title' => '加掛項目', 'method' => 'applicableList', 'icon' => 'list'],
            ['name' => 'browser', 'title' => '題目瀏覽', 'method' => 'browser', 'icon' => 'list'],
        ];
    }

    public function create()
    {
        $commFile = parent::create();

        $book = $this->file->book()->create(['title' => $this->file->title, 'lock' => false]);
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

    public function confirm()
    {
        return 'files.survey.confirm-ng';
    }

    public function applicableList()
    {
        return 'files.survey.applicableList-ng';
    }

    public function browser()
    {
        return 'files.survey.browser-ng';
    }

    public function questionBrowser()
    {
        return  View::make('files.survey.template_question_browser');
    }

    public function userApplication()
    {
        return View::make('files.survey.userApplication-ng');
    }

    public function getBook()
    {
        return ['book' => $this->file->book];
    }

    public function getQuestion()
    {
        $questions = $this->editorRepository->getQuestion(Input::get('book_id'));

        return ['questions' => $questions];
    }

    public function getAnswers()
    {
        $answers = SurveyORM\Question::find(Input::get('question_id'))->node->answers;

        return ['answers' => $answers];
    }

    public function getNodes()
    {
        $class = Input::get('root.class');

        $root = $class::find(Input::get('root.id'));

        $nodes = $this->editorRepository->getNodes($root);

        return ['nodes' => $nodes, 'paths' => $root->getPaths()];
    }

    public function createTable()
    {
        DB::table('INFORMATION_SCHEMA.COLUMNS')->where('TABLE_NAME', $this->file->book->id)->exists() && Schema::drop($this->file->book->id);

        Schema::create($this->file->book->id, function ($table) {
            $table->increments('id');
            $questions = $this->file->book->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function($carry, $page) {
                return array_merge($carry, $page->getQuestions());
            }, []);

            foreach ($questions as $question) {
                $table->text($question['id'])->nullable();
            }

            $table->integer('page_id')->nullable();
            $table->string('created_by',255);
        });

        return ['createTable' => true];
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
        list ($deleted, $questions) = $this->editorRepository->removeQuestion(Input::get('question.id'));

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

        return Survey\ApplicationRepository::book($this->file->book)->setAppliedOptions($selected);
    }

    public function getAppliedOptions()
    {
        $member_id = Input::get('member_id');

        return Survey\ApplicationRepository::book($this->file->book)->getAppliedOptions($member_id);
    }

    public function resetApplication()
    {
        return Survey\ApplicationRepository::book($this->file->book)->resetApplication();
    }

    public function setApplicableOptions()
    {
        Survey\ApplicationRepository::book($this->file->book)->setApplicableOptions(Input::get('selected'));

        return $this->getApplicableOptions();
    }

    public function getApplicableOptions()
    {
        return Survey\ApplicationRepository::book($this->file->book)->getApplicableOptions(Input::get('rowsFileId'));
    }

    public function getApplications()
    {
        $applications = $this->file->book->applications->load('members.organizations.now', 'members.user', 'members.contact');

        return ['applications' => $applications];
    }

    public function resetApplicableOptions()
    {
        Survey\ApplicationRepository::book($this->file->book)->resetApplicableOptions();

        return $this->getApplicableOptions();
    }

    public function activeExtension()
    {
        $application_id = Input::get('application_id');
        $application = $this->file->book->applications()->where('id', $application_id)->first();
        if (!$application->reject) {
            SurveyORM\Book::find($application->ext_book_id)->update(array('lock' => true));
        }
        $application->extension = !$application->extension;
        $application->save();

        return ['application' => $application];
    }

    public function reject()
    {
        $application = $this->file->book->applications()->where('id', Input::get('application_id'))->first();

        $application = Survey\ApplicationRepository::application($application)->reject();

        return ['application' => $application];
    }

    public function queryOrganizations()
    {
        $organizationDetails = \Plat\Project\OrganizationDetail::where(function($query) {
            $query->where('name', 'like', '%' . Input::get('query') . '%')->orWhere('id', Input::get('query'));
        })->limit(2000)->lists('organization_id');

        $organizations = \Plat\Project\Organization::find($organizationDetails)->load('now');

        return ['organizations' => $organizations];
    }

    public function queryUsernames()
    {
        $members_id = $this->file->book->applications->load('members')->fetch('members.id')->all();

        $usernames = \Plat\Member::with('user')->whereIn('id', $members_id)->whereHas('user', function($query) {
            $query->where('users.username', 'like', '%' . Input::get('query') . '%')->groupBy('users.username');
        })->limit(1000)->get()->fetch('user.username')->all();

        return ['usernames' => $usernames];
    }

    public function queryEmails()
    {
        $members_id = $this->file->book->applications->load('members')->fetch('members.id')->all();

        $emails = \Plat\Member::with('user')->whereIn('id', $members_id)->whereHas('user', function($query) {
            $query->where('users.email', 'like', '%' . Input::get('query') . '%');
        })->limit(1000)->get()->fetch('user.email');

        return ['emails' => $emails];
    }

    public function getApplicationPages()
    {
        $pagination = Survey\ApplicationRepository::book($this->file->book)->getApplicationPages();

        return ['currentPage' => $pagination->getCurrentPage(), 'lastPage' => $pagination->getLastPage()];
    }

    public function saveRule()
    {
        $class = Input::get('skipTarget.class');
        $root = $class::find(Input::get('skipTarget.id'));

        $rule = Survey\RuleRepository::target($root)->saveExpressions(Input::get('expressions'));

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
        $this->createTable();
        $this->file->book->update(['lock' => true]);

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

    public function sendMail()
    {
        try {
            Mail::send('emails.empty', ['context' => Input::get('context')], function($message) {
                $message->to(Input::get('email'))->subject(Input::get('title'));
            });
            return ['sended' => true];
        } catch (Exception $e){
            return ['sended' => false];
        }
    }

    public function applicationStatus()
    {
        $application = $this->file->book->applications()->OfMe()->first();
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

}