<?php

namespace Cere\Survey\Writer;

use Session;
use Auth;

class SessionWriter implements WriterInterface
{
    function __construct($book_id)
    {
        $this->book_id = $book_id;
        $this->login_id = $this->getId();
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
    public function increment($attributes = [])
    {
        $attributes = array_add($attributes, 'created_by', $this->login_id);
        $attributes = array_add($attributes, 'page_id', NULL);

        Session::put('answer.'.$this->book_id, (object)$attributes);

        return $this->all($this->login_id);
    }

    /**
     * Decrement a row in the repository.
     *
     * @param  string  $id
     * @return int|bool
     */
    public function decrement()
    {
        Session::forget('answer');

        return $this->all($this->login_id);
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
        $answers = $this->all($this->login_id);

        return isset($answers->{$key}) ? $answers->{$key} : NULL;
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
        $answers = $this->all($this->login_id);

        $answers->{$key} = $value;

        Session::put('answer.'.$this->book_id, $answers);
    }

    /**
     * Get all values of row by id in the repository.
     *
     * @return array
     */
    public function all()
    {
        $answers = Session::get('answer.'.$this->book_id);

        return $answers;
    }

    /**
     * Determine if row exists in the repository.
     *
     * @return array
     */
    public function exist()
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