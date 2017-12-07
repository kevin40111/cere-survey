<?php

namespace Cere\Survey;

use Input;
use User;
use Files;
use Mail;
use Plat\Files\CommFile;
use Cere\Survey\SurveyEditor;
use Cere\Survey\Field\SheetRepository;

class SurveyFile extends CommFile
{
    use SurveyEditor {
        SurveyEditor::__construct as private __SurveyEditorConstruct;
    }

    function __construct(Files $file, User $user)
    {
        parent::__construct($file, $user);

        if ($this->file->exists) {
            $this->__SurveyEditorConstruct(SheetRepository::target($this->file->sheets()->first())->field());
        }

        $this->book = $this->file->book;

        $this->configs = $this->file->configs->lists('value', 'name');
    }

    public function is_full()
    {
        return false;
    }

    public function get_views()
    {
        return ['open', 'demo', 'application','confirm', 'applicableList', 'browser', 'surveyTime'];
    }

    public static function tools()
    {
        return [
            ['name' => 'confirm', 'title' => '加掛審核', 'method' => 'confirm', 'icon' => 'list'],
            ['name' => 'applicableList', 'title' => '加掛項目', 'method' => 'applicableList', 'icon' => 'list'],
            ['name' => 'browser', 'title' => '題目瀏覽', 'method' => 'browser', 'icon' => 'list'],
            ['name' => 'surveyTime', 'title' => '設定時間', 'method' => 'surveyTime', 'icon' => 'alarm'],
        ];
    }

    public function create()
    {
        $commFile = parent::create();

        $sheet = $this->file->sheets()->save(SheetRepository::create()->sheet);

        SheetRepository::target($sheet)->init();

        $this->file->book()->create(['title' => $this->file->title, 'lock' => false, 'no_population' => false]);
    }

    public function queryOrganizations()
    {
        $organizationDetails = \Plat\Project\OrganizationDetail::where(function($query) {
            $query->where('name', 'like', '%' . Input::get('query') . '%')->orWhere('id', Input::get('query'));
        })->limit(2000)->lists('organization_id');

        $organizations = \Plat\Project\Organization::find($organizationDetails)->load('now');

        return ['organizations' => $organizations];
    }

    public function queryUsernames()
    {
        $members_id = $this->book->applications->load('members')->fetch('members.id')->all();

        $usernames = \Plat\Member::with('user')->whereIn('id', $members_id)->whereHas('user', function($query) {
            $query->where('users.username', 'like', '%' . Input::get('query') . '%')->groupBy('users.username');
        })->limit(1000)->get()->fetch('user.username')->all();

        return ['usernames' => $usernames];
    }

    public function queryEmails()
    {
        $members_id = $this->book->applications->load('members')->fetch('members.id')->all();

        $emails = \Plat\Member::with('user')->whereIn('id', $members_id)->whereHas('user', function($query) {
            $query->where('users.email', 'like', '%' . Input::get('query') . '%');
        })->limit(1000)->get()->fetch('user.email');

        return ['emails' => $emails];
    }

    public function sendMail()
    {
        try {
            Mail::send('emails.empty', ['context' => Input::get('context')], function($message) {
                $message->to(Input::get('email'))->subject(Input::get('title'));
            });
            return ['sended' => true];
        } catch (Exception $e){
            return ['sended' => false];
        }
    }
}
