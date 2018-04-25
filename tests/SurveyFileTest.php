<?php

use Plat\Files\SurveyFile;
use Plat\Eloquent\Survey as SurveyORM;

class SurveyFileTest extends TestCase {

    public function setUp()
    {
        parent::setUp();
        $this->app->make('artisan')->call('migrate');
        $this->seed();

        $this->setUpMember();
        $this->setUpFolder();
        $column = $this->setUpRowsFile();
        $file = $this->setUpFile($column);
        $this->surveyFile = new SurveyFile($file, $this->user);
        $this->setUpPage();
        $this->setUpNode();
        $this->setUpQuestion();
        $this->setUpAnswer();
        $this->setUpRule();
    }

    private function setUpMember()
    {
        $this->user = new User;
        $this->user->username = 'gg';
        $this->user->email    = 'tim72117@gmail.com';
        $this->user->actived = false;
        $this->user->disabled = false;
        $this->user->save();
        $this->be($this->user);

        $member = $this->user->members()->save(new \Plat\Member(['project_id' => 1, 'actived' => true]));
        $member->logined_at = Carbon\Carbon::now()->toDateTimeString();
        $member->save();
    }

    private function setUpFolder()
    {
        $folder = Files::create(['type' => 20, 'title' => '', 'created_by' => 1]);
        $folderDoc = ShareFile::create(['file_id' => $folder->id, 'target' => 'user', 'target_id' => 1, 'created_by' => 1, 'visible' => true]);
    }

    private function setUpRowsFile()
    {
        $this->rows_file = Files::create(['type' => 5, 'title' => '', 'created_by' => 1]);
        $sheet = $this->rows_file->sheets()->create(['title' => '', 'editable' => true, 'fillable' => true]);
        $table = $sheet->tables()->create(['database' => 'rows', 'name' => 'row_20150817_202236_1_0lzuu', 'lock' => false, 'construct_at' => Carbon\Carbon::now()->toDateTimeString()]);
        $column = $table->columns()->create([
            'name' => 'stdidno',
            'title' => '身分證字號',
            'rules' => 'stdidnumber',
            'unique' => false,
            'encrypt' => false,
            'isnull' => false,
            'readonly' => false,
        ]);

        return $column;
    }

    private function setUpFile($column)
    {
        $file = Files::create(['type' => 6, 'title' => '', 'created_by' => 1]);
        $book = SurveyORM\Book::create(['file_id' => $file->id, 'title' => '', 'lock' => false, 'column_id' => $column->id, 'no_population' => false]);
        $doc = ShareFile::create(['file_id' => $file->id, 'target' => 'user', 'target_id' => 1, 'created_by' => 1, 'visible' => true, 'folder_id' => 1]);

        $this->applicationRepository = Plat\Survey\ApplicationRepository::book($book);

        return $file;
    }

    private function setUpPage()
    {
        Input::replace([
            'parent' => $this->surveyFile->file->book->toArray(),
            'node' => ['type' => 'page'],
            'previous' => NULL,
        ]);
        $this->page = $this->surveyFile->createNode()['node'];
    }

    private function setUpNode()
    {
        Input::replace([
            'parent' => $this->page->toArray(),
            'node' => ['type' => 'select'],
            'previous' => NULL,
        ]);
        $this->node = $this->surveyFile->createNode()['node'];
    }

    private function setUpQuestion()
    {
        Input::replace([
            'node' => $this->node->toArray(),
            'previous' => NULL,
        ]);
        $this->question = $this->surveyFile->createQuestion()['question'];
    }

    private function setUpAnswer()
    {
        Input::replace([
            'node' => $this->node->toArray(),
            'previous' => NULL,
        ]);
        $this->answer = $this->surveyFile->createAnswer()['answer'];
    }

    private function setUpRule()
    {
        Input::replace([
            'skipTarget' => $this->node->toArray(),
            'expressions' => [
                [
                    "conditions" => [
                        [
                            "compareType" => "value",
                            "question" => $this->question->id,
                            "logic" => " > ",
                            "value" => "10",
                        ], [
                            "compareOperator" => " && ",
                            "question" => $this->question->id,
                            "logic" => " > ",
                            "compareType" => "question",
                            "value" => $this->answer->value,
                        ],
                    ],
                ],
            ],
        ]);
        $this->surveyFile->saveRule();
        $this->rule = SurveyORM\Rule::all()->first();
    }

    public function testCreate()
    {
        $amount_books = SurveyORM\Book::all()->count();

        $this->surveyFile->create();

        $this->assertCount($amount_books+1, SurveyORM\Book::all());
    }

    public function testGetQuestions()
    {
        Input::replace([
            'book_id' => $this->surveyFile->file->book->id,
        ]);

        $amount_questions = SurveyORM\question::all()->count();

        $questions = $this->surveyFile->getQuestion()['questions'];

        $this->assertCount($amount_questions, $questions);
    }

    public function testGetNodes()
    {
        Input::replace([
            'root' => $this->surveyFile->file->book->toArray(),
        ]);

        $nodes = $this->surveyFile->getNodes()['nodes'];

        $this->assertInstanceOf(Illuminate\Database\Eloquent\Collection::class, $nodes);
    }

	public function testCreateNode()
	{
        Input::replace([
            'parent' => $this->surveyFile->file->book->toArray(),
            'node' => ['type' => 'select'],
            'previous' => NULL,
        ]);

        $amount = SurveyORM\Node::all()->count();

        $node = $this->surveyFile->createNode()['node'];

        $this->assertInstanceOf(SurveyORM\Node::class, $node);

        $this->assertCount($amount+1, SurveyORM\Node::all());
    }

    public function testCreateQuestion()
    {
        Input::replace([
            'node' => $this->node->toArray(),
            'previous' => NULL,
        ]);

        $amount = SurveyORM\Question::all()->count();

        $question = $this->surveyFile->createQuestion()['question'];

        $this->assertInstanceOf(SurveyORM\Question::class, $question);

        $this->assertCount($amount+1, SurveyORM\Question::all());
    }

    public function testCreateAnswer()
    {
        Input::replace([
            'node' => $this->node->toArray(),
            'previous' => NULL,
        ]);

        $amount = SurveyORM\Answer::all()->count();

        $answer = $this->surveyFile->createAnswer()['answer'];

        $this->assertInstanceOf(SurveyORM\Answer::class, $answer);

        $this->assertCount($amount+1, SurveyORM\Answer::all());
    }

    public function testSaveNodeTitle()
    {
        $title = (string)rand(5, 15);
        $this->node->title = $title;

        Input::replace(['node' => $this->node->toArray()]);

        $this->surveyFile->saveNodeTitle();

        $this->assertSame($title, SurveyORM\Node::find($this->node->id)->title);
    }

    public function testSaveQuestionTitle()
    {
        $title = (string)rand(5, 15);
        $this->question->title = $title;

        Input::replace(['question' => $this->question->toArray()]);

        $this->surveyFile->saveQuestionTitle();

        $this->assertSame($title, SurveyORM\Question::find($this->question->id)->title);
    }

    public function testSaveAnswerTitle()
    {
        $title = (string)rand(5, 15);
        $this->answer->title = $title;

        Input::replace(['answer' => $this->answer->toArray()]);

        $this->surveyFile->saveAnswerTitle();

        $this->assertSame($title, SurveyORM\Answer::find($this->answer->id)->title);
    }

    public function testGetNodesInAnswer()
    {
        Input::replace([
            'root' => $this->answer->toArray(),
        ]);

        $amount = $this->page->childrenNodes->count();

        $nodes = $this->surveyFile->getNodes()['nodes'];

        $this->assertInstanceOf(Illuminate\Database\Eloquent\Collection::class, $nodes);

        $this->assertCount($amount, $nodes);
    }

    public function testCreateNodeInAnswer()
    {
        $nodes = SurveyORM\Node::all();

        Input::replace([
            'parent' => $this->answer->toArray(),
            'node' => ['type' => 'select'],
            'previous' => $nodes->first()->toArray(),
        ]);

        $amount = SurveyORM\Node::all()->count();

        $node = $this->surveyFile->createNode()['node'];

        $this->assertInstanceOf(SurveyORM\Node::class, $node);

        $this->assertCount($amount+1, SurveyORM\Node::all());
    }

    public function testRemoveNode()
    {
        Input::replace([
            'node' => $this->node->toArray(),
        ]);

        $amount = SurveyORM\Node::all()->count();

        $this->surveyFile->removeNode();

        $this->assertCount($amount-1, SurveyORM\Node::all());
    }

    public function testRemoveQuestion()
    {
        Input::replace([
            'question' => $this->question->toArray(),
        ]);

        $amount = SurveyORM\Question::all()->count();

        $this->surveyFile->removeQuestion();

        $this->assertCount($amount-1, SurveyORM\Question::all());
    }

    public function testRemoveAnswer()
    {
        Input::replace([
            'answer' => $this->answer->toArray(),
        ]);

        $amount = SurveyORM\Answer::all()->count();

        $this->surveyFile->removeAnswer();

        $this->assertCount($amount-1, SurveyORM\Answer::all());
    }

    public function testSetAppliedOptions()
    {
        Input::replace([
            'selected' => [
                'rules' => [
                    [
                        "conditions" => [
                            [
                                "compareType" => "value",
                                "question" => $this->question->id,
                                "logic" => " > ",
                                "value" => "10",
                            ],
                        ],
                    ],
                ],
                'columns' => [],
            ],
        ]);

        $appliedOptions = $this->surveyFile->setAppliedOptions();

        $this->assertArrayHasKey('book', $appliedOptions);
        $this->assertArrayHasKey('columns', $appliedOptions);
        $this->assertArrayHasKey('questions', $appliedOptions);
        $this->assertArrayHasKey('edited', $appliedOptions);
        $this->assertArrayHasKey('extBook', $appliedOptions);
        $this->assertArrayHasKey('extColumn', $appliedOptions);
        $this->assertArrayHasKey('organizations', $appliedOptions);
    }

    public function testGetAppliedOptions()
    {
        $appliedOptions = $this->surveyFile->getAppliedOptions();

        $this->assertArrayHasKey('book', $appliedOptions);
        $this->assertArrayHasKey('columns', $appliedOptions);
        $this->assertArrayHasKey('questions', $appliedOptions);
        $this->assertArrayHasKey('edited', $appliedOptions);
        $this->assertArrayHasKey('extBook', $appliedOptions);
        $this->assertArrayHasKey('extColumn', $appliedOptions);
        $this->assertArrayHasKey('organizations', $appliedOptions);
    }

    public function testResetApplication()
    {
        Input::replace([
            'selected' => [
                'rules' => [
                    [
                        "conditions" => [
                            [
                                "compareType" => "value",
                                "question" => $this->question->id,
                                "logic" => " > ",
                                "value" => "10",
                            ],
                        ],
                    ],
                ],
                'columns' => [],
            ],
        ]);

        $this->surveyFile->setAppliedOptions();
        $appliedOptions = $this->surveyFile->resetApplication();

        $this->assertArrayHasKey('book', $appliedOptions);
        $this->assertArrayHasKey('columns', $appliedOptions);
        $this->assertArrayHasKey('questions', $appliedOptions);
        $this->assertArrayHasKey('edited', $appliedOptions);
        $this->assertArrayHasKey('extBook', $appliedOptions);
        $this->assertArrayHasKey('extColumn', $appliedOptions);
        $this->assertArrayHasKey('organizations', $appliedOptions);
    }

    public function testSetApplicableOptions()
    {
        Input::replace([
            'selected' => [
                'questions' => [],
                'columns' => [],
                'conditionColumn' => $this->rows_file->sheets()->first()->tables()->first()->columns()->first()->toArray(),
                'tablesSelected' => $this->rows_file->id,
                'loginSelected' => [],
            ],
        ]);

        $applicableOptions = $this->surveyFile->setApplicableOptions();

        $this->assertCount(7, $applicableOptions);
    }

    public function testGetApplicableOptions()
    {
        $applicableOptions = $this->surveyFile->getApplicableOptions();

        $this->assertCount(7, $applicableOptions);
    }

    public function testGetApplications()
    {
        $applications = $this->surveyFile->getApplications()['applications'];

        $this->assertCount(0, $applications);
    }

    public function testResetApplicableOptions()
    {
        Input::replace([
            'selected' => [
                'questions' => [$this->question->id],
                'columns' => [],
                'conditionColumn' => $this->rows_file->sheets()->first()->tables()->first()->columns()->first()->toArray(),
                'tablesSelected' => $this->rows_file->id,
                'loginSelected' => [],
            ],
        ]);

        $this->surveyFile->setApplicableOptions();
        $this->surveyFile->file->book->column_id = 1;
        $this->surveyFile->file->book->rowsFile_id = 1;
        $this->surveyFile->file->book->save();

        $applicableOptions = $this->surveyFile->resetApplicableOptions();

        $this->assertNull($this->surveyFile->file->book->column_id);
        $this->assertNull($this->surveyFile->file->book->rowsFile_id);
        $this->assertCount(0, $this->applicationRepository->book->applicableOptions);
    }

    public function testReject()
    {
        $selected = [
            'rules' => [
                [
                    "conditions" => [
                        [
                            "compareType" => "value",
                            "question" => $this->question->id,
                            "logic" => " > ",
                            "value" => "10",
                        ],
                    ],
                ],
            ],
            'columns' => [],
        ];

        $this->applicationRepository->setAppliedOptions($selected);
        $application = $this->applicationRepository->book->applications()->OfMe()->first();

        Input::replace([
            'application_id' => $application->id,
        ]);
        $application = $this->surveyFile->reject()['application'];

        $this->assertInstanceOf(Plat\Eloquent\Survey\Application::class, $application);
    }

    public function testQueryOrganizations()
    {
        Input::replace([
            'query' => 'gg',
        ]);

        $organizations = $this->surveyFile->queryOrganizations()['organizations'];

        $this->assertCount(0, $organizations);
    }

    public function testQueryUsernames()
    {
        Input::replace([
            'query' => 'gg',
        ]);

        $usernames = $this->surveyFile->queryUsernames()['usernames'];

        $this->assertCount(0, $usernames);
    }

    public function testQueryEmails()
    {
        Input::replace([
            'query' => 'gg',
        ]);

        $emails = $this->surveyFile->queryEmails()['emails'];

        $this->assertCount(0, $emails);
    }

    public function testGetApplicationPages()
    {
        $paginate = $this->surveyFile->getApplicationPages();

        $this->assertArrayHasKey('currentPage', $paginate);
        $this->assertArrayHasKey('lastPage', $paginate);
        $this->assertEquals(1, $paginate['currentPage']);
        $this->assertEquals(1, $paginate['lastPage']);
    }

    public function testSaveRule()
    {
        Input::replace([
            'skipTarget' => $this->node->toArray(),
            'expressions' => [
                [
                    "conditions" => [
                        [
                            "compareType" => "value",
                            "question" => $this->question->id,
                            "logic" => " > ",
                            "value" => "10",
                        ], [
                            "compareOperator" => " && ",
                            "question" => $this->question->id,
                            "logic" => " > ",
                            "compareType" => "question",
                            "value" => $this->answer->value,
                        ],
                    ],
                ],
            ],
        ]);

        $this->surveyFile->saveRule();

        $this->assertCount(1, SurveyORM\Rule::all());
    }

    public function testDeleteRule()
    {
        Input::replace([
            'skipTarget' => $this->node->toArray(),
        ]);

        $this->surveyFile->deleteRule();

        $this->assertCount(0, SurveyORM\Rule::all());
    }

    public function testGetRule()
    {
        Input::replace([
            'skipTarget' => ['class' => SurveyORM\Node::class, 'id' => SurveyORM\Node::first()->id],
        ]);

        $rule = $this->surveyFile->getRule()['rule'];

        $this->assertCount(1, $rule->expressions);
    }

    public function testLockBook()
    {
        // $this->surveyFile->lockBook();

        // $this->assertTrue($this->applicationRepository->book->lock);
    }

    public function testCheckExtBookLocked()
    {
        Input::replace([
            'book_id' => $this->surveyFile->file->book->id,
        ]);

        $ext_locked = $this->surveyFile->checkExtBookLocked()['ext_locked'];

        $this->assertFalse($ext_locked);
    }

    public function testGetExpressionExplanation()
    {
        Input::replace([
            'rule_id' => $this->rule->id,
        ]);

        $explanation = $this->surveyFile->getExpressionExplanation();

        $this->assertStringEndsWith(' ) ', $explanation['explanation']);
        $this->assertStringStartsWith(' ( ', $explanation['explanation']);
    }

    public function testApplicationStatus()
    {
        $response = $this->surveyFile->applicationStatus();

        $this->assertArrayHasKey('status', $response);
    }
}