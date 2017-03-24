<?php

use Plat\Eloquent\Survey as SurveyORM;

class SurveyControllerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->app->make('artisan')->call('migrate');

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
        $this->book = SurveyORM\Book::create(['file_id' => $file->id, 'title' => '', 'lock' => false, 'column_id' => $column->id]);
    }

    public function testPage()
    {
        $crawler = $this->client->request('GET', 'survey/' . $this->book->id . '/survey/page');

        $this->assertTrue($this->client->getResponse()->isOk());
    }

    public function testSurveyLogin()
    {
        $crawler = $this->client->request('GET', 'survey/' . $this->book->id . '/survey/surveyLogin');

        $this->assertTrue($this->client->getResponse()->isOk());
    }

    public function testGetSurveyQuesion()
    {

    }

    public function testGetBook()
    {
        $this->client->request('POST', 'survey/' . $this->book->id . '/survey/getBook', []);

        $this->assertArrayHasKey('book', $this->client->getResponse()->original);
    }

    public function testGetNextNode()
    {
        // $page = $this->book->childrenNodes()->save(new SurveyORM\Node(['type' => 'page']));

        // $input = [
        //     'next' => true,
        // ];

        // $this->client->request('POST', 'survey/' . $this->book->id . '/survey/getNextNode', $input);

        // $this->assertArrayHasKey('node', $this->client->getResponse()->original);
    }

    public function testGetNextNodes()
    {
        $page = $this->book->childrenNodes()->save(new SurveyORM\Node(['type' => 'page']));

        $input = [
            'page' => $page->toArray(),
        ];

        $this->client->request('POST', 'survey/' . $this->book->id . '/survey/getNextNodes', $input);

        $this->assertArrayHasKey('nodes', $this->client->getResponse()->original);
    }

    public function testGetChildren()
    {
        $node = $this->book->childrenNodes()->save(new SurveyORM\Node(['type' => 'select']));

        $input = [
            'parent' => $node->toArray(),
        ];

        $this->client->request('POST', 'survey/' . $this->book->id . '/survey/getChildren', $input);

        $this->assertArrayHasKey('nodes', $this->client->getResponse()->original);
    }

    public function testGetRule()
    {
        $node = $this->book->childrenNodes()->save(new SurveyORM\Node(['type' => 'select']));

        $input = [
            'skipTarget' => $node->toArray(),
        ];

        $this->client->request('POST', 'survey/' . $this->book->id . '/survey/getRule', $input);

        $this->assertArrayHasKey('rule', $this->client->getResponse()->original);
    }
}