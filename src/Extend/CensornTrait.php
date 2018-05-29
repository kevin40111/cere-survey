<?php

namespace Cere\Survey\Extend;

use Input;
use View;
use Cere\Survey\RuleRepository;
use Cere\Survey\Eloquent\Rule;
use Files;
use Cere\Survey\Eloquent as SurveyORM;

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
            'start_at' => $due['start'],
            'close_at' => $due['close'],
        ];
    }

    public function setApplicationStatus()
    {
        $application = $this->hook->applications()->findOrFail(Input::get('id'));

        $application->update(Input::only('status'));;
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

        $mainBookPages = $this->hook->book->childrenNodes->reduce(function ($carry, $page) use ($application) {

            $questions = $page->getQuestions()->each(function ($question) use ($application) {
                $question->selected = in_array($question->id, $application->fields);
            });

            array_push($carry, ['fields' => $questions]);

            return $carry;
        }, []);

        return [
            'mainBookPages' => $mainBookPages,
            'mainListFields' => $mainListFields,
        ];

    }

    public function updateIndividualStatus()
    {
        $application = $this->hook->applications->find(Input::get('id'));

        $application->individual_status = Input::get('data');

        if ($application->individual_status['apply'] == 2 || $application->individual_status['book'] == 2) {
            $application->status = 2;
        }

        $application->save();

        return $application;
    }

    public function loadOperation()
    {
        $application = $this->hook->applications->find(Input::get('id'));

        $fields = Files::find(($this->hook->book->auth['fieldFile_id']))->sheets->first()->tables->first()->columns;

        return [
            'application' => $application->load('operations'),
            'fields' => $fields,
        ];
    }

    public function createOperation()
    {
        $application = $this->hook->applications->find(Input::get('id'));

        $operation = $application->operations()->create(['operator' => '==']);

        return ['application' => $application->load('operations')];
    }

    public function deleteOperation()
    {
        $application = $this->hook->applications->find(Input::get('id'));

        $application->operations->each(function ($operation) {
            $operation->delete();
        });

        return ['deleted' => ! $application->operations()->exists()];
    }

    public function createFactor()
    {
        $class = Input::get('target.class');
        $target = $class::find(Input::get('target.id'));

        $factor = RuleRepository::find(Input::get('operation.id'))->createFactor(Input::get('factor', []), $target);

        return ['factor' => $factor];
    }

    public function updateFactorTarget()
    {
        $class = Input::get('target.class');
        $target = $class::find(Input::get('target.id'));

        $updated = RuleRepository::updateFactorTarget(Input::get('factor.id'), $target);

        return ['updated' => $updated];
    }

    public function updateFactor()
    {
        $updated = RuleRepository::updateFactor(Input::get('factor.id'), Input::get('factor'));

        return ['updated' => $updated];
    }

    public function loadSkiper()
    {
        $skiper = SurveyORM\Rule\Skiper::findOrFail(Input::get('skiper.id'))->load('operations');

        return ['skiper' => $skiper];
    }
}
