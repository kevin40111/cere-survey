<?php

namespace Cere\Survey\Extend;

use Cere\Survey\Eloquent as SurveyORM;
use Auth;
use Input;
use DB;
use Cere\Survey\Eloquent\Field\Field;

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

    public function setApplicableOptions($selected)
    {
        $extend = $this->book->extend;
        if (! isset($extend)) {
            $this->book->extend()->save(new SurveyORM\ExtendRule(['rule' => $selected]));
        } else {
            $this->book->extend->update(['rule' => $selected]);
        }
    }

    public function getApplicableOptions()
    {
        $file = \Files::find($this->book->auth['fieldFile_id']);

        $extend = $this->book->extend ?: new SurveyORM\ExtendRule;

        $optionColumns = !is_null($file) ? $file->sheets->first()->tables->first()->columns->each(function ($column) use ($extend) {
            $column->selected = in_array($column->id, $extend->rule['fields']);
        }) : [];

        $optionQuestions = $this->book->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) use ($extend) {
            $questions = $page->getQuestions();

            foreach ($questions as &$question) {
                $question["selected"] = in_array($question['id'], $extend->rule['fields']);
            }

            return $carry + [$page->id => $questions];
        }, []);

        return [
            'rule' => $extend->rule,
            'options' => [
                'columns' => $optionColumns,
                'questions' => $optionQuestions,
            ],
        ];
    }

    private function getConditionColumn()
    {
        return \Row\Column::find($this->book->column_id);
    }

    private function getParentList()
    {
        $no_population = \Files::all()->filter(function ($file) {
            return ($file->created_by ==  Auth::user()->id) ? (is_null($file->book) ? false : $file->book->no_pop_id) : false;
        })->map(function($file) {
            return $file->book->no_pop_id;
        })->toArray();
        return $this->book->file->select('id', 'title')->where('created_by', '=', Auth::user()->id)->where('type','=','30')->whereNotIn('id', $no_population)->get();
    }

    private function resetApplicableOptions()
    {
        $this->deleteRelatedApplications();
        $this->deleteApplicableOptions();
        $this->deleteCondition();
    }

    private function deleteRelatedApplications()
    {
        $this->book->applications->each(function($application){
            $application->delete();
        });
    }

    private function deleteApplicableOptions()
    {
        $this->book->applicableOptions()->delete();
    }

    private function deleteCondition()
    {
        $book = $this->book;
        $book->column_id = NULL;
        $book->rowsFile_id = NULL;
        $book->no_population = 0;
        $book->lock = false;
        $book->loginRow_id = NULL;
        $book->save();
    }

    public function getApplicationPages()
    {
        $members_id = $this->book->applications->load('members')->fetch('members.id')->all();
        return \Plat\Member::with('user')->whereIn('id', $members_id)->paginate(10);
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

        $extBook = SurveyORM\Book::find($application->ext_book_id);
        RuleRepository::target($extBook)->saveExpressions($selected['rules']);

        $application->appliedOptions()->sync($selected['columns']);

        return $this->getAppliedOptions();
    }

    public function getAppliedOptions($member_id = NULL)
    {
        $application = isset($member_id) ? $this->book->applications()->where('member_id', Input::get('member_id'))->first() : $this->book->applications()->OfMe()->first();
        if ($application) {
            $appliedOptions =  $application->appliedOptions->load('surveyApplicableOption')->groupBy(function($applicableOption) {
                return $applicableOption->survey_applicable_option_type == Field::class ? 'applicableColumns' : 'applicableQuestions';
            });
            $edited = true;
            $options = $appliedOptions;

            $extBook = SurveyORM\Book::find($application->ext_book_id);
            $extBookDoc = $extBook->file->docs()->OfMe()->first();
            $rule = RuleRepository::target($extBook)->getRule();
            $organizationsSelected = array_map(function($rule){
                return \Plat\Project\OrganizationDetail::find($rule['value']);
            }, $rule->expressions[0]['conditions']);
        } else {
            $applicableOption = $this->book->applicableOptions->load('surveyApplicableOption')->groupBy(function($applicableOption) {
                return $applicableOption->survey_applicable_option_type == Field::class ? 'applicableColumns' : 'applicableQuestions';
            });
            $edited = false;
            $options = $applicableOption;
            $extBookDoc = [];
            $organizationsSelected = [];
        }

        $columns = isset($options['applicableColumns']) ? $options['applicableColumns'] : [];
        $questions = isset($options['applicableQuestions']) ? $options['applicableQuestions'] : [];
        $extColumn = \Row\Column::find($this->book->column_id);
        $organizations = Auth::user()->members()->Logined()->orderBy('logined_at', 'desc')->first()->organizations->map(function($organization){
            return $organization->now;
        })->toArray();

        return [
            'book' => $this->book,
            'columns' => $columns,
            'questions' => $questions,
            'edited' => $edited,
            'extBook' => $extBookDoc,
            'extColumn' => $extColumn,
            'organizations' => [
                'lists' => $organizations,
                'selected' => $organizationsSelected,
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

    private function createApplication()
    {
        return $this->book->applications()->create([
            'member_id' => Auth::user()->members()->Logined()->orderBy('logined_at', 'desc')->first()->id,
        ]);
    }

    public function reject()
    {
        if (!$this->application->reject) {
            SurveyORM\Book::find($this->application->ext_book_id)->update(array('lock' => false));
        }
        $this->application->reject = !$this->application->reject;
        $this->application->save();

        return $this->application;
    }
}