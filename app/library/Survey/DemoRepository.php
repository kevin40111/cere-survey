<?php

namespace Plat\Survey;

use Session;
use Auth;

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

        return $this->all($id);
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

        return (object)[];
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
        $answer = Session::get('answer.'.$this->book_id.'.'.$key);

        return $answer;
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

        return (object)$answers;
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
        $user_id = Auth::user()->id;

        return $user_id;
    }

    /**
     * Get login type.
     *
     * @return mixed
     */
    public function getType()
    {

        return 'demo';
    }
}