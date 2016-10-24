<?php

namespace Plat\Eloquent\Survey;

use Eloquent;

class Node extends Eloquent {

    protected $table = 'survey_nodes';

    public $timestamps = false;

    protected $fillable = ['type', 'title'];

    protected $attributes = ['title' => ''];

    public function book()
    {
        return $this->hasOne('Plat\Eloquent\Survey\Book', 'id', 'book_id');
    }

    public function questions()
    {
        return $this->hasMany('Plat\Eloquent\Survey\Question', 'node_id', 'id');
    }

    public function answers()
    {
        return $this->hasMany('Plat\Eloquent\Survey\Answer', 'node_id', 'id');
    }

}