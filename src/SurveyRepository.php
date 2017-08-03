<?php

namespace Cere\Survey;

use DB;
use Cere\Survey\SurveySession;
use Cere\Survey\Field\FieldRepository;

class SurveyRepository implements SurveyRepositoryInterface
{
    function __construct($book_id)
    {
        $book = \Cere\Survey\Eloquent\Book::find($book_id);
        $this->fieldRepository = FieldRepository::target($book->file->sheets()->first()->tables()->first());
        $this->book_id = $book_id;
        $this->login_id = SurveySession::getHashId();
    }

    public static function create($book_id)
    {
        return new self($book_id);
    }

    /**
     * Increment a row in the repository.
     *
     * @param  string  $id
     * @param  array   $default
     * @return int|bool
     */
    public function increment($default = [])
    {
        $this->fieldRepository->insert(array_merge(['encrypt_id' => $this->login_id], $default));
    }

    /**
     * Decrement a row in the repository.
     *
     * @param  string  $id
     * @return int|bool
     */
    public function decrement()
    {
        $this->fieldRepository->deleteRow(['encrypt_id' => $this->login_id]);
    }

    /**
     * Retrieve a value from the repository by id in the repository.
     *
     * @param  string  $id
     * @param  string  $key
     * @return mixed
     */
    public function get($key)
    {
        $answer = $this->fieldRepository->getFieldData(['encrypt_id' => $this->login_id], $key);

        return $answer->value;
    }

    /**
     * Store an value in the repository.
     *
     * @param  string  $id
     * @param  string  $key
     * @param  string     $value
     * @return void
     */
    public function put($key, $value)
    {
        $values = $this->fieldRepository->setAttributesFieldName([$key => $value]);

        $this->fieldRepository->put(['encrypt_id' => $this->login_id], $values);
    }

    public function setPage($id, $value)
    {
        $this->put($id, 'page_id', $value);
    }

    /**
     * Get all values of row by id in the repository.
     *
     * @return array
     */
    public function all()
    {
        $answers = $this->fieldRepository->getRow(['encrypt_id' => $this->login_id]);

        return $answers;
    }

    /**
     * Determine if row exists in the repository.
     *
     * @return array
     */
    public function exist()
    {
        $existed = $this->fieldRepository->rowExists(['encrypt_id' => $this->login_id]);

        return $existed;
    }

    /**
     * Get user ID.
     *
     * @return mixed
     */
    public function getId()
    {
        $user_id = SurveySession::getHashId();

        return $user_id;
    }

    /**
     * Get login type.
     *
     * @return mixed
     */
    public function getType()
    {
        return 'survey';
    }
}