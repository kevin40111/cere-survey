<?php

namespace Cere\Survey\Extend\Apply;

use Cere\Survey\Eloquent as SurveyORM;
use Auth;
use Input;
use DB;
use Cere\Survey\Eloquent\Field\Field;
use Cere\Survey\RuleRepository;

class ApplicationRepository
{
    function __construct($book)
    {
        $this->book = $book;
    }

    public static function book($book)
    {
        return new self($book);
    }

    public static function application($application)
    {
        $instance = new self($application->book);

        $instance->application = $application;

        return $instance;
    }

    public function setAppliedOptions($selected)
    {
        $application = $this->book->applications()->OfMe()->withTrashed()->first();

        if ($application) {
            $application->restore();
        } else {
            $application = $this->createApplication();
            $extDoc = $this->createExtBook();
            $application->ext_book_id = \ShareFile::find($extDoc['id'])->isFile->book->id;
            $application->save();
        }

        $application->appliedFields()->sync($selected['optionFields']);

        return $this->getAppliedOptions();
    }

    public function getAppliedOptions()
    {
        $member = Auth::user()->members()->orderBy('logined_at', 'desc')->first();

        $application = $this->book->extendHook->applications()->where('member_id', $member->id)->first();

        $appliedFields = ! $application ? [] : $application->appliedFields->lists('id');

        return [
            'optionFields' => Field::find($this->book->extendHook->options['fields'])->each(function($optionField) use ($appliedFields) {
                $optionField->selected = in_array($optionField->id, $appliedFields);
            }),
            'limit' => [
                'mainBook' => $this->book->extendHook->options['columnsLimit'],
                'mainList' => $this->book->extendHook->options['fieldsLimit'],
            ],
        ];
    }

    public function resetApplication()
    {
        $application = $this->book->applications()->OfMe()->withTrashed()->first();
        $application->reject = false;
        $application->save();
        $extBook = SurveyORM\Book::find($application->ext_book_id);
        RuleRepository::target($extBook)->deleteRule();
        $this->book->applications()->OfMe()->delete();
        return $this->getAppliedOptions();
    }

    private function createExtBook()
    {
        $newDoc = ['title' => $this->book->title .'(加掛題本)', 'type' => 6];

        Input::replace(['fileInfo' => $newDoc]);

        $user = Auth::user();

        $doc = \ShareFile::whereNull('folder_id')->first();

        $folderComponent = new \Plat\Files\FolderComponent($doc->is_file, $user);

        $folderComponent->setDoc($doc);

        return $folderComponent->createComponent()['doc'];
    }
}
