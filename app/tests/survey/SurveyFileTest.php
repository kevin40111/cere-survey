<?php

use Plat\Files\SurveyFile;
use Plat\Eloquent\Survey as SurveyORM;

class SurveyFileTest extends TestCase {

    public function setUp()
    {
        parent::setUp();
        $this->app->make('artisan')->call('migrate');
        $this->user = new User;
        $this->user->username = 'gg';
        $this->user->email    = 'tim72117@gmail.com';
        $this->user->actived = false;
        $this->user->disabled = false;
        $this->user->save();
        Auth::login($this->user);

        $rows_file = Files::create(['id' => 2, 'type' => 5, 'title' => '', 'created_by' => 1]);
        $sheet = $rows_file->sheets()->create(['title' => '', 'editable' => true, 'fillable' => true]);
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

        $file = Files::create(['id' => 1, 'type' => 6, 'title' => '', 'created_by' => 1]);
        $book = SurveyORM\Book::create(['file_id' => $file->id, 'title' => '', 'lock' => false, 'column_id' => $column->id]);
        $doc = ShareFile::create(['file_id' => $file->id, 'target' => 'user', 'target_id' => 1, 'created_by' => 1, 'visible' => true, 'folder_id' => 1]);

        $this->surveyFile = new SurveyFile($file, $this->user);

        Input::replace([
            'parent' => $this->surveyFile->file->book->toArray(),
            'node' => ['type' => 'page'],
            'previous' => NULL,
        ]);
        $this->page = $this->surveyFile->createNode()['node'];

        Input::replace([
            'parent' => $this->page->toArray(),
            'node' => ['type' => 'select'],
            'previous' => NULL,
        ]);
        $this->node = $this->surveyFile->createNode()['node'];

        Input::replace([
            'node' => $this->node->toArray(),
            'previous' => NULL,
        ]);
        $this->question = $this->surveyFile->createQuestion()['question'];

        Input::replace([
            'node' => $this->node->toArray(),
            'previous' => NULL,
        ]);
        $this->answer = $this->surveyFile->createAnswer()['answer'];

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

    public function testGetAnswers()
    {
        Input::replace([
            'question_id' => $this->question->id,
        ]);

        $answers = $this->surveyFile->getAnswers()['answers'];

        $this->assertCount($answers->count(), SurveyORM\Answer::all());
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

    public function testGetApplicationPages()
    {
        $currentPage = $this->surveyFile->getApplicationPages()['currentPage'];

        $this->assertEquals(1, $currentPage);
    }

    public function testGetConditionColumn()
    {
        $this->surveyFile->file->book->column_id = 1;
        $this->surveyFile->file->book->save();

        $column = $this->surveyFile->getConditionColumn();

        $this->assertInstanceOf(Row\Column::class, $column);
    }

    public function testSetConditionColumns()
    {
        $this->surveyFile->setConditionColumns(['id' => 1]);

        $this->assertEquals(1, $this->surveyFile->file->book->column_id);
    }

    public function testDeleteCondition()
    {
        $this->surveyFile->file->book->column_id = 1;
        $this->surveyFile->file->book->rowsFile_id = 1;
        $this->surveyFile->file->book->save();

        $this->surveyFile->deleteCondition();

        $this->assertNull($this->surveyFile->file->book->column_id);
        $this->assertNull($this->surveyFile->file->book->rowsFile_id);
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
        //$locked = $this->surveyFile->lockBook()['lock'];

        //$this->assertTrue($locked);
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
}