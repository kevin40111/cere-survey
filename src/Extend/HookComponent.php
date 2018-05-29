<?php

namespace Cere\Survey\Extend;

use User;
use Files;
use ShareFile;
use Plat\Member;
use Plat\Files\CommFile;
use Cere\Survey\Extend\ApplySettingTrait;
use Cere\Survey\Extend\CensornTrait;
use Cere\Survey\Extend\MessageTrait;
use Cere\Survey\Extend\InviteTrait;
use Plat\Files\FolderComponent;
use Cere\Survey\Extend\Apply\ApplicationRepository;
use Input;
use Redirect;
use RequestFile;
use Cere\Survey\Eloquent\Book;
use Cere\Survey\Eloquent\Extend\Application;

class HookComponent extends CommFile
{
    use ApplySettingTrait;

    use CensornTrait;

    use InviteTrait;

    use MessageTrait;

    function __construct(Files $file, User $user)
    {
        parent::__construct($file, $user);

        $this->hook = $this->file->hook;
    }

    public function is_full()
    {
        return false;
    }

    public function get_views()
    {
        return ['open', 'extendHook', 'confirm', 'invites'];
    }

    public static function tools()
    {
        return [
            ['name' => 'extendHook', 'title' => '加掛設定', 'method' => 'extendHook', 'icon' => 'list'],
            ['name' => 'confirm', 'title' => '加掛審核', 'method' => 'confirm', 'icon' => 'list'],
            ['name' => 'invites', 'title' => '加掛邀請', 'method' => 'invites', 'icon' => 'list'],
        ];
    }

    public function open()
    {
        return $this->extendHook();
    }

    public function invite()
    {
        Input::merge(['fileInfo' => ['type' => 31, 'title' => $this->hook->title . ' 加掛題本']]);

        $applications = Member::find(Input::get('members'))->map(function ($member) {
            $folderComponent = new FolderComponent($this->doc->folder->isFile, $member->user);
            $folderComponent->setDoc($this->doc->folder);
            $doc = $folderComponent->createComponent()['doc'];
            $component = ShareFile::find($doc['id']);
            $component->update(['created_by', $member->user->id]);

            $application = new Application;
            $application->member()->associate($member);
            $application->book()->associate($component->isFile->book);

            RequestFile::updateOrCreate([
                'target' => 'user',
                'target_id' => $member->user->id,
                'doc_id' => $doc['id'],
                'created_by' => $this->user->id,
            ], [
                'disabled' => false,
                'description' => $this->hook->book->title . ' 加掛邀請'
            ]);

            return $application;
        })->all();

        $this->hook->applications()->saveMany($applications);

        return ['result' => true];
    }

    public function getBrowserQuestions()
    {
        $book = Book::find(Input::get('book_id'));

        return ['pages' => Browser::getQuestions($book)];
    }
}