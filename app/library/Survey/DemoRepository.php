<?php

namespace Plat\Survey;

use Session;

class DemoRepository implements SurveyRepositoryInterface
{
    function __construct($book_id)
    {
        $this->book_id = $book_id;
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
        $attributes = array_add($default, 'created_by', $id);

        Session::put('answer.'.$this->book_id, $attributes);

        return Session::get('answer.'.$this->book_id);
    }

    /**
     * Decrement a row in the repository.
     *
     * @param  string  $id
     * @return int|bool
     */
    public function decrement($id)
    {
        Session::forget('answer');

        return Session::get('answer');
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
        $answers = Session::get('answer.'.$this->book_id.'.'.$key);

        return $answers;
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
        $answers = Session::put('answer.'.$this->book_id.'.'.$key, $value);

        return Session::has('answer.'.$this->book_id.'.'.$key);
    }

    /**
     * Get all values of row by id in the repository.
     *
     * @return array
     */
    public function all($id)
    {
        $answers = Session::get('answer.'.$this->book_id);

        return $answers;
    }

    /**
     * Determine if row exists in the repository.
     *
     * @return array
     */
    public function exist($id)
    {
        return Session::has('answer.'.$this->book_id);
    }

    /**
     * Get user ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return 1;
    }
}