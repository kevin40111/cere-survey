<?php

namespace Plat\Survey;

interface SurveyRepositoryInterface
{
    /**
     * Increment a row in the repository.
     *
     * @param  string  $id
     * @param  array   $default
     * @return int|bool
     */
    public function increment($id, $default = []);

    /**
     * Decrement a row in the repository.
     *
     * @param  string  $id
     * @return int|bool
     */
    public function decrement($id);

    /**
     * Retrieve a value from the repository by id in the repository.
     *
     * @param  string  $id
     * @param  string  $key
     * @return mixed
     */
    public function get($id, $key);

    /**
     * Store an value in the repository.
     *
     * @param  string  $id
     * @param  string  $key
     * @param  string     $value
     * @return void
     */
    public function put($id, $key, $value);

    /**
     * Get all values of row by id in the repository.
     *
     * @return array
     */
    public function all($id);

    /**
     * Determine if row exists in the repository.
     *
     * @return array
     */
    public function exist($id);

    /**
     * Get user ID.
     *
     * @return mixed
     */
    public function getId();
}