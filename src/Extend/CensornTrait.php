<?php

namespace Cere\Survey\Extend;

use Input;
use View;

trait CensornTrait
{
    public function confirm()
    {
        return 'survey::extend.setting.censorn';
    }

    public function userApplication()
    {
        return View::make('survey::extend.setting.userApplication');
    }

    public function getApplications()
    {
        $applications = $this->book->extendHook->applications->load('member.organizations.now', 'member.user', 'member.contact', 'book');

        return ['applications' => $applications];
    }

    public function reject()
    {
        $application = $this->book->extendHook->applications()->where('id', Input::get('application_id'))->first();

        if (!$this->application->reject) {
            SurveyORM\Book::find($this->application->ext_book_id)->update(array('lock' => false));
        }
        $this->application->reject = !$this->application->reject;
        $this->application->save();

        return $this->application;

        return ['application' => $application];
    }

    public function getApplicationPages()
    {
        $member_id = $this->book->extendHook->applications->load('member')->fetch('member.id')->all();
        return \Plat\Member::with('user')->whereIn('id', $member_id)->paginate(10);
    }

    public function activeExtension()
    {
        $application_id = Input::get('application_id');
        $application = $this->book->extendHook->applications()->where('id', $application_id)->first();
        if (!$application->reject) {
            SurveyORM\Book::find($application->ext_book_id)->update(array('lock' => true));
        }
        $application->extension = !$application->extension;
        $application->save();

        return ['application' => $application];
    }

    public function resetApplication()
    {
        $application = $this->book->applications()->OfMe()->withTrashed()->first();
        $application->reject = false;
        $application->save();
        $extBook = SurveyORM\Book::find($application->ext_book_id);
        RuleRepository::target($extBook)->deleteRule();
        $this->book->applications()->OfMe()->delete();
        return $this->getAppliedOptions();
    }

    public function getAppliedOptions()
    {
        $application = $this->book->extendHook->applications->find(Input::get('id'));

        $columns = $this->book->sheet->tables->first()->columns->filter(function ($column) use ($application) {
            return in_array($column->id, $application->fields);
        })->toArray();

        $questions =$this->book->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) use ($application){
            $questions = $page->getQuestions();

            $questions = array_filter($questions, function($question) use ($application){
                return in_array($question['id'], $application->fields);
            });

            array_push($carry, $questions);

            return $carry;
        }, []);

        return [
            'application' => $application->load('member.user'),
            'fields' => [
                'mainBookPages' => $questions,
                'mainList' => $columns,
            ],
        ];

    }

    private function deleteRelatedApplications()
    {
        $this->book->extendHook->applications->each(function($application){
            $application->delete();
        });
    }

    public function updateIndividualStatus()
    {
        $application = $this->book->extendHook->applications->find(Input::get('id'));

        $application->individual_status = Input::get('data');

        $application->save();

        return $application;
    }
}
