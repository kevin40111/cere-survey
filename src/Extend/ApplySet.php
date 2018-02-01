<?php

namespace Cere\Survey\Extend;

use Input;

trait ApplySet
{
    public function confirm()
    {
        return 'survey::extend.confirm-ng';
    }

    public function applicableList()
    {
        return 'survey::extend.applicableList-ng';
    }

    public function setApplicableOptions()
    {
        SettingRepository::book($this->book)->setApplicableOptions(Input::get('selecteds'));
    }

    public function getApplicableOptions()
    {
        return SettingRepository::book($this->book)->getApplicableOptions();
    }
}
