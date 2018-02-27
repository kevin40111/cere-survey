<?php

namespace Cere\Survey\Extend;

use Input;
use View;
use Cere\Survey\RuleRepository;
use Cere\Survey\Eloquent\Rule;
use Carbon\Carbon;

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
        $applications = $this->hook->applications->load('member.organizations.now', 'member.user', 'member.contact', 'book');
        $due = $this->hook->due;

        return [
            'hook' => $this->hook,
            'applications' => $applications,
            'start_at' => Carbon::parse($due['start'])->tz('Asia/Taipei')->toDateTimeString(),
            'close_at' => Carbon::parse($due['close'])->tz('Asia/Taipei')->toDateTimeString(),
        ];
    }

    public function getApplicationPages()
    {
        $member_id = $this->hook->applications->load('member')->fetch('member.id')->all();
        return \Plat\Member::with('user')->whereIn('id', $member_id)->paginate(10);
    }

    public function getAppliedOptions()
    {
        $application = $this->hook->applications->find(Input::get('id'));

        $fieldFile = \Files::find($this->hook->book->auth['fieldFile_id']);

        $mainListFields = !is_null($fieldFile) ? $fieldFile->sheets->first()->tables->first()->columns->each(function ($column) use ($application) {
            $column->selected = in_array($column->id, $application->fields);
        }) : [];

        $mainBookPages =$this->hook->book->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) use ($application){
            $questions = $page->getQuestions();

            foreach ($questions as &$question) {
                $question['selected'] = in_array($question['id'], $application->fields);
            }

            array_push($carry, ['fields' => $questions]);

            return $carry;
        }, []);

        return [
            'mainBookPages' => $mainBookPages,
            'mainListFields' => $mainListFields,
        ];

    }

    private function deleteRelatedApplications()
    {
        $this->hook->applications->each(function($application){
            $application->delete();
        });
    }

    public function updateIndividualStatus()
    {
        $application = $this->hook->applications->find(Input::get('id'));

        $application->individual_status = Input::get('data');

        $application->save();

        return $application;
    }

    public function getApplicationHangingRule()
    {
        $application = $this->hook->applications->find(Input::get('id'));

        $fields =  $application->hook->book->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) {
            $questions = $page->getQuestions();
            return $carry = array_merge($carry, $questions);
        }, []);

        return [
            'fields' => $fields,
            'rule' => $application->book->rule ? $application->book->rule : new Rule(['expressions' => [['conditions' => [['compareType' => 'question']]]]]),
        ];
    }

    public function setApplicationHangingRule()
    {
        $application = $this->hook->applications->find(Input::get('id'));

        $page = $this->hook->book->sortByPrevious(['childrenNodes'])->childrenNodes->last();

        RuleRepository::target($application->book)->saveExpressions(Input::get('rule'), 'direction', $page->id);

        return ['rule' => RuleRepository::target($application->book)->getRule()];
    }
}
