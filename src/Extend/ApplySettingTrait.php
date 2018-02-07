<?php

namespace Cere\Survey\Extend;

use Input;
use Cere\Survey\Extend\Setting\SettingRepository;

trait ApplySettingTrait
{
    public function applicableList()
    {
        return 'survey::extend.setting.applicableList-ng';
    }

    public function setApplicableOptions()
    {
        SettingRepository::book($this->book)->setApplicableOptions(Input::get('selecteds'));
    }

    public function getApplicableOptions()
    {
        return SettingRepository::book($this->book)->getApplicableOptions();
    }

    public function getConsent()
    {
        return SettingRepository::book($this->book)->getConsent();
    }

    public function setConsent()
    {
        return SettingRepository::book($this->book)->setConsent(Input::get('consent'));
    }
}
