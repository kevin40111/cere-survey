<?php

namespace Cere\Survey\Extend;

use Cere\Survey\Eloquent as SurveyORM;
use Auth;

class SettingRepository
{
    function __construct($book)
    {
        $this->book = $book;
    }

    public static function book($book)
    {
        return new self($book);
    }

    public function setApplicableOptions($selecteds)
    {
        $extend = $this->book->extend;
        if (! isset($extend)) {
            $this->book->extend()->save(new SurveyORM\ExtendRule(['rule' => $selecteds]));
        } else {
            $this->book->extend->update(['rule' => $selecteds]);
        }
    }

    public function getApplicableOptions()
    {
        $file = \Files::find($this->book->auth['fieldFile_id']);

        $extend = $this->book->extend ?: new SurveyORM\ExtendRule;

        $optionColumns = !is_null($file) ? $file->sheets->first()->tables->first()->columns->each(function ($column) use ($extend) {
            $column->selected = in_array($column->id, $extend->rule['fields']);
        }) : [];

        $pages = $this->book->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) use ($extend) {
            $questions = $page->getQuestions();

            foreach ($questions as &$question) {
                $question["selected"] = in_array($question['id'], $extend->rule['fields']);
            }

            return $carry + [$page->id => $questions];
        }, []);

        return [
            'rule' => $extend->rule,
            'options' => [
                'columns' => $optionColumns,
                'pages' => $pages,
            ],
        ];
    }

    private function deleteRelatedApplications()
    {
        $this->book->applications->each(function($application){
            $application->delete();
        });
    }

    public function getApplicationPages()
    {
        $members_id = $this->book->applications->load('members')->fetch('members.id')->all();
        return \Plat\Member::with('user')->whereIn('id', $members_id)->paginate(10);
    }

    private function createApplication()
    {
        return $this->book->applications()->create([
            'member_id' => Auth::user()->members()->Logined()->orderBy('logined_at', 'desc')->first()->id,
        ]);
    }

    public function reject()
    {
        if (!$this->application->reject) {
            SurveyORM\Book::find($this->application->ext_book_id)->update(array('lock' => false));
        }
        $this->application->reject = !$this->application->reject;
        $this->application->save();

        return $this->application;
    }
}
