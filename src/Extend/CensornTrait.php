<?php

namespace Cere\Survey\Extend;

use Input;
use View;
use Cere\Survey\RuleRepository;
use Cere\Survey\Eloquent\Rule;

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

        return ['applications' => $applications];
    }

    public function reject()
    {
        $application = $this->hook->applications()->where('id', Input::get('application_id'))->first();

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
        $member_id = $this->hook->applications->load('member')->fetch('member.id')->all();
        return \Plat\Member::with('user')->whereIn('id', $member_id)->paginate(10);
    }

    public function activeExtension()
    {
        $application_id = Input::get('application_id');
        $application = $this->hook->applications()->where('id', $application_id)->first();
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
        $application = $this->hook->applications->find(Input::get('id'));

        $fieldFile = \Files::find($this->book->auth['fieldFile_id']);

        $columns = !is_null($fieldFile) ? $fieldFile->sheets->first()->tables->first()->columns->each(function ($column) use ($application) {
            $column->selected = in_array($column->id, $application->fields);
        }) : [];

        $questions =$this->book->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) use ($application){
            $questions = $page->getQuestions();

            foreach ($questions as &$question) {
                $question['selected'] = in_array($question['id'], $application->fields);
            }

            array_push($carry, ['questions' => $questions]);

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

        $page = $this->book->sortByPrevious(['childrenNodes'])->childrenNodes->last();

        RuleRepository::target($application->book)->saveExpressions(Input::get('rule'), 'direction', $page->id);

        return ['rule' => RuleRepository::target($application->book)->getRule];
    }
}
