<?php

namespace Cere\Survey\Writer;

use Cere\Survey\Field\FieldRepository;
use Cere\Survey\Eloquent\Book;

class FieldWriter implements WriterInterface
{
    function __construct($book_id, $user)
    {
        $book = Book::find($book_id);
        $this->fieldRepository = FieldRepository::target($book->sheet->tables()->first(), $book->file->created_by);
        $this->user = $user;
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
        $this->fieldRepository->insert(array_merge(['encrypt_id' => $this->getId()], $default));
    }

    /**
     * Decrement a row in the repository.
     *
     * @param  string  $id
     * @return int|bool
     */
    public function decrement()
    {
        $this->fieldRepository->deleteRow(['encrypt_id' => $this->user->id]);
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
        $answer = $this->fieldRepository->getFieldData(['encrypt_id' => $this->user->id], $key);

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

        $this->fieldRepository->put(['encrypt_id' => $this->user->id], $values);
    }

    public function setPage($value)
    {
        $this->fieldRepository->put(['encrypt_id' => $this->user->id], ['page_id' => $value]);
    }

    /**
     * Get all values of row by id in the repository.
     *
     * @return array
     */
    public function all()
    {
        $answers = $this->fieldRepository->getRow(['encrypt_id' => $this->user->id]);

        return $answers;
    }

    /**
     * Determine if row exists in the repository.
     *
     * @return array
     */
    public function exist()
    {
        $existed = $this->fieldRepository->rowExists(['encrypt_id' => $this->getId()]);

        return $existed;
    }

    /**
     * Get user ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->user->id;
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

    public function user()
    {
        return $this->user;
    }
}