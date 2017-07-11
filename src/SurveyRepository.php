<?php

namespace Cere\Survey;

use DB;
use Cere\Survey\SurveySession;
use Cere\Survey\Field\FieldRepository;

class SurveyRepository implements SurveyRepositoryInterface
{
    function __construct($book_id)
    {
        $book = \Plat\Eloquent\Survey\Book::find($book_id);

        $this->fieldRepository = FieldRepository::target(\Files::find($book->file_id)->sheets()->first()->tables()->first());
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
    public function increment($id, $default = [])
    {
        $this->fieldRepository->insert(array_merge(['encrypt_id' => $id], $default));
    }

    /**
     * Decrement a row in the repository.
     *
     * @param  string  $id
     * @return int|bool
     */
    public function decrement($id)
    {
        $this->fieldRepository->deleteRow(['encrypt_id' => $id]);
    }

    /**
     * Retrieve a value from the repository by id in the repository.
     *
     * @param  string  $id
     * @param  string  $key
     * @return mixed
     */
    public function get($id, $key)
    {
        $answer = $this->fieldRepository->getFieldData(['encrypt_id' => $id], $key);

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
    public function put($id, $key, $value)
    {
        $values = $this->fieldRepository->setAttributesFieldName([$key => $value]);

        $this->fieldRepository->put(['encrypt_id' => $id], $values);
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
    public function all($id)
    {
        $answers = $this->fieldRepository->getRow(['encrypt_id' => $id]);

        return $answers;
    }

    /**
     * Determine if row exists in the repository.
     *
     * @return array
     */
    public function exist($id)
    {
        $existed = $this->fieldRepository->rowExists(['encrypt_id' => $id]);

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