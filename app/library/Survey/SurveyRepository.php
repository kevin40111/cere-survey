<?php

namespace Plat\Survey;

use DB;

class SurveyRepository implements SurveyRepositoryInterface
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

        DB::table($this->book_id)->insert($attributes);
    }

    /**
     * Decrement a row in the repository.
     *
     * @param  string  $id
     * @return int|bool
     */
    public function decrement($id)
    {

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

    }

    /**
     * Get all values of row by id in the repository.
     *
     * @return array
     */
    public function all($id)
    {

    }

    /**
     * Determine if row exists in the repository.
     *
     * @return array
     */
    public function exist($id)
    {
        return false;
    }
}