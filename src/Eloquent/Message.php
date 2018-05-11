<?php

namespace Cere\Survey\Eloquent;

use Eloquent;

class Message extends Eloquent
{
    protected $connection = 'survey';

    protected $table = 'survey_messages';

    public $timestamps = true;

    protected $fillable = ['user_id', 'content', 'title'];
}
