<?php

namespace Cere\Survey\Extend;

use User;
use Files;
use ShareFile;
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
        return ['open', 'contract', 'extendHook', 'confirm', 'invites'];
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

    public function contract()
    {
        return 'survey::extend.apply.contract';
    }

    public function agreeContract()
    {
        Input::replace(['fileInfo' => ['type' => 31, 'title' => $this->hook->title . ' 加掛題本']]);

        $folderComponent = new FolderComponent($this->doc->folder->isFile, $this->user);

        $folderComponent->setDoc($this->doc->folder);

        $doc = $folderComponent->createComponent()['doc'];

        $component = ShareFile::find($doc['id']);

        $book = $component->isFile->book()->create(['title' => $component->isFile->title, 'lock' => false]);

        $member = $this->user->members()->logined()->orderBy('logined_at', 'desc')->first();

        ApplicationRepository::create($this->hook, $book, $member);

        $this->doc->requesteds()->where('created_by', $this->user->id)->delete();

        RequestFile::updateOrCreate([
            'target' => 'user',
            'target_id' => 1,
            'doc_id' => $component->id,
            'created_by' => $this->user->id,
            'disabled' => false,
        ], [
            'description' => $component->isFile->title . ' 加掛申請'
        ]);

        return Redirect::to($doc['link']);
    }

    public function invite()
    {
        foreach (Input::get('users') as $user_id) {
            RequestFile::updateOrCreate([
                'target' => 'user',
                'target_id' => $user_id,
                'doc_id' => $this->doc->id,
                'created_by' => $this->user->id,
                'disabled' => false,
            ], [
                'description' => $this->hook->book->title . ' 加掛邀請'
            ]);
        }

        return ['result' => true];
    }

    public function getBrowserQuestions()
    {
        $book = Book::find(Input::get('book_id'));

        return ['pages' => Browser::getQuestions($book)];
    }
}