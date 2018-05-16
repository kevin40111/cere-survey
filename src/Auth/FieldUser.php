<?php

namespace Cere\Survey\Auth;

use \Files;
use Cere\Survey\Field\FieldRepository;
use Cere\Survey\Eloquent\Field\Field;

class FieldUser
{
    public $id;

    protected $logined;

    function __construct($book)
    {
        $this->book = $book;
        $this->session = new SurveySession($book->id);
        $this->id = $this->session->encrypt();
    }

    public static function demo($book)
    {
        $session = SurveySession::create($book->id, json_encode([]));

        return new static($book);
    }

    public function login($input)
    {
        $this->logout();

        $table = Files::find($this->book->auth['fieldFile_id'])->sheets->first()->tables->first();
        $fieldRepository = FieldRepository::target($table, $this->book->file->created_by);

        $validator = empty($this->book->auth['validFields']) ? 'validNew' : 'validExist';
        list($exist, $attributes) = $this->$validator($fieldRepository, $input);

        if ($exist) {
            $this->session = SurveySession::create($this->book->id, json_encode($attributes));
            $this->id = $this->session->encrypt();
            $fieldRepository->put($attributes, ['encrypt_id' => $this->id]);
        }
    }

    private function validExist($fieldRepository, $input)
    {
        $checkValuesByID = Field::find($this->book->auth['validFields'])->map(function($field) use ($input) {
            return ['id' => $field->id, 'value' => $input[$field->name]];
        });

        $saveValuesByID = Field::find(array_except($this->book->auth['inputFields'], $this->book->auth['validFields']))->map(function($field) use ($input) {
            return ['id' => $field->id, 'value' => $input[$field->name]];
        });

        $checkValuesByName = $fieldRepository->setAttributesFieldName($checkValuesByID->lists('value', 'id'));
        $saveValuesByName = $fieldRepository->setAttributesFieldName($saveValuesByID->lists('value', 'id'));

        $exists = $fieldRepository->rowExists($checkValuesByName);

        if ($exists) {
            $fieldRepository->put($checkValuesByName, $saveValuesByName);
        }

        return [$exists, $checkValuesByName];
    }

    private function validNew($fieldRepository, $input)
    {
        $saveValuesByID = Field::find($this->book->auth['inputFields'])->map(function($field) use ($input) {
            return ['id' => $field->id, 'value' => $input[$field->name]];
        });

        $saveValuesByName = $fieldRepository->setAttributesFieldName($saveValuesByID->lists('value', 'id'));

        if (! $fieldRepository->rowExists($saveValuesByName)) {
            $fieldRepository->insert($saveValuesByName);
        }

        return [true, $saveValuesByName];
    }

    public function sign($book)
    {
        SurveySession::create($book->id, $this->session->userinfo());
    }

    public function logout()
    {
        $this->session->destroy();
    }

    public function logined()
    {
        return $this->session->exists();
    }

    public function information()
    {
        $table = Files::find($this->book->auth['fieldFile_id'])->sheets->first()->tables->first();
        $fieldRepository = FieldRepository::target($table, $this->book->file->created_by);

        return $fieldRepository->getRow(['encrypt_id' => $this->id]);
    }
}
