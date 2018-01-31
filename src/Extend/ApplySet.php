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
        ApplicationRepository::book($this->book)->setApplicableOptions(Input::get('selected'));
    }

    public function getApplicableOptions()
    {
        return ApplicationRepository::book($this->book)->getApplicableOptions(Input::get('rowsFileId'));
    }
}
