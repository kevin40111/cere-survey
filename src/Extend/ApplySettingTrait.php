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
        HookRepository::book($this->book)->setApplicableOptions(Input::get('selecteds'));
    }

    public function getApplicableOptions()
    {
        return HookRepository::book($this->book)->getApplicableOptions();
    }

    public function getConsent()
    {
        return HookRepository::book($this->book)->getConsent();
    }

    public function setConsent()
    {
        return HookRepository::book($this->book)->setConsent(Input::get('consent'));
    }
}
