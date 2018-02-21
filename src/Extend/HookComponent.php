<?php

namespace Cere\Survey\Extend;

use User;
use Files;
use ShareFile;
use Plat\Files\CommFile;
use Cere\Survey\Extend\ApplySettingTrait;
use Cere\Survey\Extend\CensornTrait;
use Plat\Files\FolderComponent;
use Cere\Survey\Extend\Apply\ApplicationRepository;
use Input;
use Redirect;

class HookComponent extends CommFile
{
    use ApplySettingTrait;

    use CensornTrait;

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
        return ['open', 'contract', 'extendHook', 'confirm'];
    }

    public static function tools()
    {
        return [
            ['name' => 'extendHook', 'title' => '加掛設定', 'method' => 'extendHook', 'icon' => 'list'],
            ['name' => 'confirm', 'title' => '加掛審核', 'method' => 'confirm', 'icon' => 'list'],
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

        return Redirect::to($doc['link']);
    }
}