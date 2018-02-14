<?php

namespace Cere\Survey;

use Input;
use User;
use Files;
use Mail;
use Plat\Files\CommFile;
use Cere\Survey\SurveyEditor;
use Cere\Survey\Field\SheetRepository;
use Cere\Survey\Field\FieldComponent;
use Cere\Survey\Extend\ApplySettingTrait;
use Cere\Survey\Extend\CensornTrait;
use Plat\Files\FolderComponent;
use Cere\Survey\Extend\Apply\ApplicationRepository;
use ShareFile;
use Redirect;

class SurveyFile extends CommFile
{
    use SurveyEditor {
        SurveyEditor::__construct as private __SurveyEditorConstruct;
    }

    use ApplySettingTrait;

    use CensornTrait;

    function __construct(Files $file, User $user)
    {
        parent::__construct($file, $user);

        if ($this->file->exists) {
            $this->__SurveyEditorConstruct(SheetRepository::target($this->file->book->sheet)->field());
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
        return ['open', 'demo', 'application','confirm', 'extendHook', 'browser', 'surveyTime', 'loginCondition', 'contract'];
    }

    public static function tools()
    {
        return [
            ['name' => 'confirm', 'title' => '加掛審核', 'method' => 'confirm', 'icon' => 'list'],
            ['name' => 'confirm', 'title' => '登入設定', 'method' => 'loginCondition', 'icon' => 'list'],
            ['name' => 'extendHook', 'title' => '加掛設定', 'method' => 'extendHook', 'icon' => 'list'],
            ['name' => 'browser', 'title' => '題目瀏覽', 'method' => 'browser', 'icon' => 'list'],
            // ['name' => 'surveyTime', 'title' => '設定時間', 'method' => 'surveyTime', 'icon' => 'alarm'],
        ];
    }

    /**
     * @todo to static
     **/
    public function create()
    {
        parent::create();

        $this->book = $this->file->book()->create(['title' => $this->file->title, 'lock' => false]);

        $fieldComponent = FieldComponent::createComponent(['title' => $this->file->title], $this->user);

        $this->book->sheet()->associate($fieldComponent->file->sheets()->first());

        $this->book->save();

        return $this;
    }

    public function contract()
    {
        return 'survey::extend.apply.contract';
    }

    public function agreeContract()
    {
        Input::replace(['fileInfo' => ['type' => 31, 'title' => $this->file->title . ' 加掛題本']]);

        $folderComponent = new FolderComponent($this->doc->folder->isFile, $this->user);

        $folderComponent->setDoc($this->doc->folder);

        $doc = $folderComponent->createComponent()['doc'];

        $component = ShareFile::find($doc['id']);

        $book = $component->isFile->book()->create(['title' => $component->isFile->title, 'lock' => false]);

        $fieldComponent = FieldComponent::createComponent(['title' => $component->isFile->title], $this->user);

        $book->sheet()->associate($fieldComponent->file->sheets()->first());

        $book->save();

        $member = $this->user->members()->logined()->orderBy('logined_at', 'desc')->first();

        ApplicationRepository::create($this->book->extendHook, $book->id, $member->id);

        return Redirect::to($doc['link']);
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

    public function exportSheet()
    {
        SheetRepository::target($this->book->sheet)->exportAllRows();
    }
}
