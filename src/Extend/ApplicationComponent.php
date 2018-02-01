<?php

namespace Cere\Survey\Extend;

use User;
use Files;
use Cere\Survey\Eloquent as SurveyORM;
use Input;
use View;
use Plat\Files\CommFile;
use Cere\Survey\SurveyEditor;
use Cere\Survey\Field\SheetRepository;
use Cere\Survey\Field\FieldComponent;
use Cere\Survey\Extend\Apply\ApplicationRepository;

class ApplicationComponent extends CommFile
{
    use SurveyEditor {
        SurveyEditor::__construct as private __SurveyEditorConstruct;
    }

    function __construct(Files $file, User $user)
    {
        parent::__construct($file, $user);

        $this->configs = $this->file->configs->lists('value', 'name');

        $this->mainBook = SurveyORM\book::find($this->configs['main_book_id']);

        if (! $this->file->book) {
            $this->create();
        }

        $this->book = $this->file->book;

        $this->__SurveyEditorConstruct(SheetRepository::target($this->book->sheet)->field());
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

    public function is_full()
    {
        return false;
    }

    public function get_views()
    {
        return ['contract', 'open', 'application'];
    }

    public function contract()
    {
        return 'survey::extend.apply.contract';
    }

    public function application()
    {
        return 'survey::extend.apply.application-ng';
    }

    public function userApplication()
    {
        return View::make('survey::extend.apply.userApplication-ng');
    }

    public function setAppliedOptions()
    {
        $selected = Input::get('selected');

        return ApplicationRepository::book($this->mainBook)->setAppliedOptions($selected);
    }

    public function getAppliedOptions()
    {
        return ApplicationRepository::book($this->mainBook)->getAppliedOptions();
    }

    public function resetApplication()
    {
        return ApplicationRepository::book($this->mainBook)->resetApplication();
    }

    public function checkExtBookLocked()
    {
        $locked = SurveyORM\Book::find(Input::get('book_id'))->lock;

        return  ['ext_locked' => $locked];
    }

    public function applicationStatus()
    {
        $application = $this->mainBook->applications()->OfMe()->first();
        if (is_null($application)) {
            return ['status' => null];
        } else {
            if ($application->extension ==  $application->reject) {
                $status = '0';
            } else if ($application->reject) {
                $status = '1';
            } else {
                $status = '2';
            }
            return ['status' => $status];
        }
    }
}
