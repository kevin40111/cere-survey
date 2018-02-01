<?php

namespace Cere\Survey\Extend;

use Input;

trait CensornTrait
{
    public function confirm()
    {
        return 'survey::extend.setting.confirm-ng';
    }

    public function getApplications()
    {
        $applications = $this->book->extendHook->applications->load('members.organizations.now', 'members.user', 'members.contact');

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
        $members_id = $this->book->extendHook->applications->load('members')->fetch('members.id')->all();
        return \Plat\Member::with('user')->whereIn('id', $members_id)->paginate(10);
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

    private function deleteRelatedApplications()
    {
        $this->book->extendHook->applications->each(function($application){
            $application->delete();
        });
    }
}
