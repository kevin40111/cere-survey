<?php

namespace Cere\Survey\Extend;

use User;
use Files;
use Cere\Survey\Eloquent as SurveyORM;
use Input;
use Redirect;
use View;
use Plat\Files\CommFile;
use Cere\Survey\SurveyEditor;
use Cere\Survey\Field\SheetRepository;
use Cere\Survey\Field\FieldComponent;
use Cere\Survey\Extend\Apply\ApplicationRepository;
use Request;

class ApplicationComponent extends CommFile
{
    use SurveyEditor {
        SurveyEditor::__construct as private __SurveyEditorConstruct;
    }

    function __construct(Files $file, User $user)
    {
        parent::__construct($file, $user);

        $this->configs = $this->file->configs->lists('value', 'name');

        if ($this->file->book) {
            $this->book = $this->file->book;
            $this->__SurveyEditorConstruct(SheetRepository::target($this->book->sheet)->field());
        }
    }

    public function is_full()
    {
        return false;
    }

    public function get_views()
    {
        return ['open'];
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

    public function open()
    {
        if ($this->book->application->agree) {
            return ApplicationRepository::instance($this->book->application)->getStep()['view'];
        } else {
            return 'survey::extend.apply.contract';
        }
    }

    public function agreeContract()
    {
        $this->book->application->update(['agree' => true]);

        return Redirect::back();
    }

    public function stepsTemplate()
    {
        View::share('step', $this->book->application->step);

        return View::make('survey::extend.apply.steps');
    }

    public function setAppliedOptions()
    {
        $selected = Input::get('selected');

        return ApplicationRepository::instance($this->book->application)->setAppliedOptions($selected);
    }

    public function getConsent()
    {
        return ApplicationRepository::instance($this->book->application)->getConsent();
    }

    public function getAppliedOptions()
    {
        return ApplicationRepository::instance($this->book->application)->getAppliedOptions();
    }

    public function getBookFinishQuestions()
    {
        return ApplicationRepository::instance($this->book->application)->getBookFinishQuestions();
    }

    public function nextStep()
    {
        $messages = ApplicationRepository::instance($this->book->application)->nextStep();

        return ['messages' => $messages];
    }

    public function preStep()
    {
        $messages = ApplicationRepository::instance($this->book->application)->preStep();

        return ['messages' => $messages];
    }

    public function backToEdit()
    {
        ApplicationRepository::instance($this->book->application)->backToEdit();
    }

    public function backToApply()
    {
        ApplicationRepository::instance($this->book->application)->backToApply();
    }

    public function getMessages()
    {
        return ['messages' => $this->book->application->messages()->orderBy('updated_at', 'desc')->get()];
    }

    public function getBrowserQuestions()
    {
        return ['pages' => Browser::getQuestions($this->book)];
    }
}
