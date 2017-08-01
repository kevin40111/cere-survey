<?php

namespace Cere\Survey;

use DB;
use Cere\Survey\SurveySession;

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

        return $attributes;
    }

    /**
     * Decrement a row in the repository.
     *
     * @param  string  $id
     * @return int|bool
     */
    public function decrement($id)
    {
        DB::table($this->book_id)->where('created_by', $id)->delete();
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
        $answer = DB::table($this->book_id)->where('created_by', $id)->select($key.' as value')->first();

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
        $answers = DB::table($this->book_id)->where('created_by', $id)->update(array($key => $value));
    }

    /**
     * Get all values of row by id in the repository.
     *
     * @return array
     */
    public function all($id)
    {
        $answers = DB::table($this->book_id)->where('created_by', $id)->first();

        return $answers;
    }

    /**
     * Determine if row exists in the repository.
     *
     * @return array
     */
    public function exist($id)
    {
        $existed = DB::table($this->book_id)->where('created_by', $id)->exists();
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