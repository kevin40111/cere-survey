<?php

namespace Cere\Survey\Extend;

use Input;
use Cere\Survey\Extend\Setting\HookRepository;

trait ApplySettingTrait
{
    public function extendHook()
    {
        return 'survey::extend.setting.hook';
    }

    public function setApplicableOptions()
    {
        HookRepository::instance($this->hook)->setApplicableOptions(Input::get('name'), Input::get('options'));
    }

    public function getApplicableOptions()
    {
        return HookRepository::instance($this->hook)->getApplicableOptions();
    }

    public function getConsent()
    {
        return HookRepository::instance($this->hook)->getConsent();
    }

    public function setConsent()
    {
        return HookRepository::instance($this->hook)->setConsent(Input::get('consent'));
    }
}
