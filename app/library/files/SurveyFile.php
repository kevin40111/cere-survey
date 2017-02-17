<?php
namespace Plat\Files;

use DB;
use Schema;
use Input, View;
use User;
use Files;
use Auth;
use Plat\Survey;
use Plat\Eloquent\Survey as SurveyORM;

class SurveyFile extends CommFile {

    function __construct(Files $file, User $user)
    {
        parent::__construct($file, $user);

        $this->configs = $this->file->configs->lists('value', 'name');
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
        $questions = $this->file->book->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) {
            return array_merge($carry, $page->getQuestions());
        }, []);
        return ['questions' => $questions];
    }

    public function getNodes()
    {
        $class = Input::get('root.class');

        $root = $class::find(Input::get('root.id'));

        if ($root->childrenNodes->isEmpty()) {
            $type = get_class($root) == 'Plat\Eloquent\Survey\Book' ? 'page' : 'explain';
            $node = $root->childrenNodes()->save(new SurveyORM\Node(['type' => $type]));

            $root->load('childrenNodes');
        }

        $nodes = $root->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions.node', 'answers', 'byRules'])->each(function($node) {
            $node->sortByPrevious(['questions', 'answers']);
        });

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

            $table->integer('page_id');
            $table->integer('created_by');
        });

        $this->file->book->update(['lock' => true]);

        return ['lock' => true];
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
        
        $this->updateAnswerValue(SurveyORM\Node::find($answer->node_id));

        return ['answer' => $answer];
    }

    public function removeNode()
    {
        $node = SurveyORM\Node::find(Input::get('node.id'));
        if ($node->next) {
            $previous_id = $node->previous ? $node->previous->id : NULL;
            $node->next->update(['previous_id' => $previous_id]);
        }
        $deleted =  $node->deleteNode();

        return ['deleted' => $deleted];
    }

    public function removeQuestion()
    {
        $question = SurveyORM\Question::find(Input::get('question.id'));
        if ($question->next) {
            $previous_id = $question->previous ? $question->previous->id : NULL;
            $question->next->update(['previous_id' => $previous_id]);
        }
        $childrenNodes = $question->childrenNodes->each(function($subNode) {
            $subNode->deleteNode();
        });

        return ['deleted' => $question->delete(), 'questions' => $question->node->questions];
    }

    public function removeAnswer()
    {
        $answer = SurveyORM\Answer::find(Input::get('answer.id'));
        if ($answer->next) {
            $previous_id = $answer->previous ? $answer->previous->id : NULL;
            $answer->next->update(['previous_id' => $previous_id]);
        }
        $childrenNodes = $answer->childrenNodes->each(function($subNode) {
            $subNode->deleteNode();
        });

        return ['deleted' => $answer->delete(), 'answers' => $answer->node->answers, 'update' => $this->updateAnswerValue($answer->node)];
    }

    public function moveUp()
    {
        $class = '\\' . Input::get('item.class');

        $relation = Input::get('item.relation');

        $item = $class::find(Input::get('item.id'))->moveUp();

        if ($class == '\\Plat\Eloquent\Survey\Answer') {
            $this->updateAnswerValue($item->node);
        }

        return ['items' => $item->node->sortByPrevious([$relation])->$relation];
    }

    public function moveDown()
    {
        $class = '\\' . Input::get('item.class');

        $relation = Input::get('item.relation');

        $item = $class::find(Input::get('item.id'))->moveDown();
        if ($class == '\\Plat\Eloquent\Survey\Answer') {
            $this->updateAnswerValue($item->node);
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

    public function getBooks()
    {
        return ['books' => Set\Book::all()];
    }

    public function getColumns()
    {
        $columns = \ShareFile::find(Input::get('file_id'))->isFile->sheets->first()->tables->first()->columns;

        return ['columns' => $columns];
    }

    public function setAppliedOptions()
    {
        $selected = Input::get('selected');
        $application = $this->file->book->applications()->OfMe()->withTrashed()->first();
        if ($application) {
            $application->restore();
        } else {
            $application = $this->createApplication();
            $extDoc = $this->createExtBook();
            $extBook = $this->setExtBook($application, $extDoc['id']);
        }
        Input::replace(['skipTarget' => ['class' => $this->file->book->class, 'id' => $application->ext_book_id], 'rules' => $selected['rules']]);
        $this->saveRules();
        $application->appliedOptions()->sync($selected['columns']);
        $appliedOptions = $this->getAppliedOptions();
        return $appliedOptions;
    }

    public function createExtBook()
    {
        $newDoc = ['title' => $this->file->book->title .'(加掛題本)', 'type' => 6];

        Input::replace(['fileInfo' => $newDoc]);

        $user = Auth::user();

        $doc = $this->getPaths()->first();

        $folderComponent = new \Plat\Files\FolderComponent($doc->is_file, $user);

        $folderComponent->setDoc($doc);

        return $folderComponent->createComponent()['doc'];
    }

    public function getAppliedOptions()
    {
        $member_id = Input::get('member_id');
        $application = isset($member_id) ? $this->file->book->applications()->where('member_id', Input::get('member_id'))->first() : $this->file->book->applications()->OfMe()->first();
        if ($application) {
            $appliedOptions =  $application->appliedOptions->load('surveyApplicableOption')->groupBy(function($applicableOption) {
                return $applicableOption->survey_applicable_option_type == 'Row\Column' ? 'applicableColumns' : 'applicableQuestions';
            });
            $edited = true;
            $options = $appliedOptions;
            $extBook = $this->getExtBook($application->ext_book_id);
            Input::replace(['skipTarget' => ['class' => $this->file->book->class, 'id' => $application->ext_book_id]]);
            $rules = $this->getRules();
            $organizationsSelected = array_map(function($rule){
                return \Plat\Project\OrganizationDetail::find($rule['value']);
            }, $rules[0]['conditions']);
        } else {
            $applicableOption = $this->file->book->applicableOptions->load('surveyApplicableOption')->groupBy(function($applicableOption) {
                return $applicableOption->survey_applicable_option_type == 'Row\Column' ? 'applicableColumns' : 'applicableQuestions';
            });
            $edited = false;
            $options = $applicableOption;
            $extBook = [];
            $organizationsSelected = [];
        }

        $columns = isset($options['applicableColumns']) ? $options['applicableColumns'] : [];
        $questions = isset($options['applicableQuestions']) ? $options['applicableQuestions'] : [];
        $extColumn = \Row\Column::find($this->file->book->column_id);
        $organizations = Auth::user()->members()->Logined()->orderBy('logined_at', 'desc')->first()->organizations->map(function($organization){
            return $organization->now;
        })->toArray();

        return [
            'book' => $this->file->book,
            'columns' => $columns,
            'questions' => $questions,
            'edited' => $edited,
            'extBook' => $extBook,
            'extColumn' => $extColumn,
            'organizations' => [
                'lists' => $organizations,
                'selected' => $organizationsSelected,
            ],
        ];
    }

    public function resetApplication()
    {

        $application = $this->file->book->applications()->OfMe()->withTrashed()->first();
        Input::replace(['skipTarget' => ['class' => $this->file->book->class, 'id' => $application->ext_book_id]]);
        $this->deleteApplication();
        $this->deleteRules();
        return $this->getAppliedOptions();
    }

    public function createApplication()
    {
        return $this->file->book->applications()->create([
            'book_id' => Input::get('book_id'),
            'member_id' => Auth::user()->members()->Logined()->orderBy('logined_at', 'desc')->first()->id,
        ]);
    }

    public function deleteApplication()
    {
        $this->file->book->applications()->OfMe()->first()->delete();
    }


    public function setRowsFile($rows_file_id)
    {
        $this->file->book->update(['rowsFile_id' => $rows_file_id]);
    }

    public function deleteRelatedApplications()
    {
        $this->file->book->applications->each(function($application){
            $application->delete();
        });

    }

    public function setApplicableOptions()
    {
        $this->file->book->optionColumns()->sync(Input::get('selected.columns'));
        $this->file->book->optionQuestions()->sync(Input::get('selected.questions'));
        $this->setConditionColumns(Input::get('selected.conditionColumn'));
        $this->setRowsFile(Input::get('selected.tablesSelected'));
        return $this->getApplicableOptions();
    }

    public function getParentList()
    {
        return $this->file->select('id', 'title')->where('created_by', '=', Auth::user()->id)->where('type','=','5')->get();
    }

    public function getMotherList()
    {
        $motherList = $this->file->select('id', 'title')->where('created_by', '=', Auth::user()->id)->where('type','=','5')->get();
        return ['motherList' =>  $motherList];
    }

    public function getApplicableOptions()
    {
        $conditionColumn = [];
        $edited = !$this->file->book->optionColumns->isEmpty() || !$this->file->book->optionQuestions->isEmpty();
        if ($edited) {
            $columns = $this->file->book->optionColumns;
            $questions = $this->file->book->optionQuestions;
            $conditionColumn = $this->getConditionColumn();
            $rowsFile_id = $this->file->book->rowsFile_id;
            $parentSelected = Files::find($rowsFile_id);
            $parentList = [];
        } else {
            $file = Files::find(Input::get('rowsFileId'));
            $columns = !is_null($file) ? $file->sheets->first()->tables->first()->columns : [];
            $questions = $this->file->book->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) {
                return array_merge($carry, $page->getQuestions());
            }, []);
            $parentSelected = [];
            $parentList = $this->getParentList();
        }

        return [
            'columns' => $columns,
            'questions' => $questions,
            'edited' => $edited,
            'conditionColumn' => $conditionColumn,
            'tables' => [
                'list' => $parentList,
                'selected' => $parentSelected,
            ],
        ];
    }

    public function getApplications()
    {
        $applications = $this->file->book->applications->load('members.organizations.now', 'members.user', 'members.contact');
        return ['applications' => $applications];
    }

    public function resetApplicableOptions()
    {
        $this->deleteRelatedApplications();
        $this->deleteApplicableOptions();
        $this->deleteCondition();
        return $this->getApplicableOptions();
    }

    public function deleteApplicableOptions()
    {
        $this->file->book->applicableOptions()->delete();
    }

    public function activeExtension()
    {
        $application_id = Input::get('application_id');
        $extension = $this->file->book->applications()->where('id', $application_id)->first();
        $this->file->book->applications()->where('id', $application_id)->update(array('extension' => !$extension->extension));

        return ['application' => $this->file->book->applications()->where('id', $application_id)->first()];
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
        $members_id = $this->file->book->applications->load('members')->fetch('members.id')->all();
        $members = \Plat\Member::with('user')->whereIn('id', $members_id)->paginate(10);

        return ['currentPage' => $members->getCurrentPage(), 'lastPage' => $members->getLastPage()];
    }

    public function setExtBook(&$application, $doc_id)
    {
        $application->ext_book_id = \ShareFile::find($doc_id)->isFile->book->id;
        $application->save();
    }

    public function getExtBook($book_id)
    {
        $doc = SurveyORM\Book::find($book_id)->file->docs()->OfMe()->first();
        return  \Struct_file::open($doc);
    }

    /*public function deleteDoc($docId)
    {
        \ShareFile::find($docId)->delete();
    }*/

    public function getConditionColumn()
    {
        $column_id = $this->file->book->column_id;
        return \Row\Column::find($column_id);

    }

    public function setConditionColumns($conditionColumn)
    {
        $book = $this->file->book;
        $book->column_id = $conditionColumn['id'];
        $book->save();
    }

    public function deleteCondition()
    {
        $book = $this->file->book;
        $book->column_id = NULL;
        $book->rowsFile_id = NULL;
        $book->save();
    }

    public function saveRules()
    {
        $class = Input::get('skipTarget.class');
        $root = $class::find(Input::get('skipTarget.id'));
        $rules = Input::get('rules');
        if ($root->rules()->first() == null) {
            $rule = SurveyORM\Rule::create(array('expression' => $rules));
            $root->rules()->attach($rule->id);
        } else {
            $root->rules()->first()->update(['expression' => $rules]);
        }

        return 'save rules successed';
    }

    public function deleteRules()
    {
        $class = Input::get('skipTarget.class');
        $root = $class::find(Input::get('skipTarget.id'));
        if (!$root->rules()->first() == null) {
            $ruleId = $root->rules()->first()->id;
            $root->rules()->detach($ruleId);
            SurveyORM\Rule::find($ruleId)->delete();
        }

        return 'delete rules successed';
    }

    public function getRules()
    {
        $class = Input::get('skipTarget.class');
        $root = $class::find(Input::get('skipTarget.id'));

        if (!$root->rules()->first() == null) {
            $rules = $root->rules()->first()->expression;
        } else {
            $rules = null;
        }

        return $rules;
    }

    public function updateAnswerValue($node)
    {
        $answersInNode = $node->sortByPrevious(['answers'])->answers;
        
        foreach ($answersInNode as $key => $answerInNode) {
            $answerInNode->update(['value' =>$key]);
        }

        return true;
    }
}