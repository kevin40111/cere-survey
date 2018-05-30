<?php

namespace Cere\Survey\Eloquent;

use Eloquent;
use User;

class Message extends Eloquent
{
    protected $connection = 'survey';

    protected $table = 'survey_messages';

    public $timestamps = true;

    protected $fillable = ['user_id', 'content', 'title'];

    public function from()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
