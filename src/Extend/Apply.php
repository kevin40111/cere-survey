<?php

namespace Cere\Survey\Extend;

use Cere\Survey\Eloquent as SurveyORM;
use Input;
use View;

trait Apply
{
    public function application()
    {
        return 'survey::extend.application-ng';
    }

    public function confirm()
    {
        return 'survey::extend.confirm-ng';
    }

    public function applicableList()
    {
        return 'survey::extend.applicableList-ng';
    }

    public function userApplication()
    {
        return View::make('survey::extend.userApplication-ng');
    }

    public function setAppliedOptions()
    {
        $selected = Input::get('selected');

        return ApplicationRepository::book($this->book)->setAppliedOptions($selected);
    }

    public function getAppliedOptions()
    {
        $member_id = Input::get('member_id');

        return ApplicationRepository::book($this->book)->getAppliedOptions($member_id);
    }

    public function resetApplication()
    {
        return ApplicationRepository::book($this->book)->resetApplication();
    }

    public function setApplicableOptions()
    {
        ApplicationRepository::book($this->book)->setApplicableOptions(Input::get('selecteds'));
    }

    public function getApplicableOptions()
    {
        return ApplicationRepository::book($this->book)->getApplicableOptions(Input::get('rowsFileId'));
    }

    public function getApplications()
    {
        $applications = $this->book->applications->load('members.organizations.now', 'members.user', 'members.contact');

        return ['applications' => $applications];
    }

    public function activeExtension()
    {
        $application_id = Input::get('application_id');
        $application = $this->book->applications()->where('id', $application_id)->first();
        if (!$application->reject) {
            SurveyORM\Book::find($application->ext_book_id)->update(array('lock' => true));
        }
        $application->extension = !$application->extension;
        $application->save();

        return ['application' => $application];
    }

    public function reject()
    {
        $application = $this->book->applications()->where('id', Input::get('application_id'))->first();

        $application = ApplicationRepository::application($application)->reject();

        return ['application' => $application];
    }

    public function getApplicationPages()
    {
        $pagination = ApplicationRepository::book($this->book)->getApplicationPages();

        return ['currentPage' => $pagination->getCurrentPage(), 'lastPage' => $pagination->getLastPage()];
    }

    public function checkExtBookLocked()
    {
        $locked = SurveyORM\Book::find(Input::get('book_id'))->lock;

        return  ['ext_locked' => $locked];
    }

    public function applicationStatus()
    {
        $application = $this->book->applications()->OfMe()->first();
        if (is_null($application)) {
            return ['status' => null];
        } else {
            if ($application->extension ==  $application->reject) {
                $status = '0';
            } else if ($application->reject) {
                $status = '1';
            } else {
                $status = '2';
            }
            return ['status' => $status];
        }
    }
}
