<?php

namespace Cere\Survey\Eloquent\Extend;

use Eloquent;
use Cere\Survey\Eloquent\Book;
use Files;

class Hook extends Eloquent {

    protected $connection = 'survey';

    protected $table = 'survey_extend_hooks';

    public $timestamps = false;

    protected $fillable = ['title', 'file_id', 'main_book_limit', 'main_list_limit', 'consent', 'due'];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function file()
    {
        return $this->belongsTo(Files::class, 'file_id');
    }

    public function getMainBookLimitAttribute($options)
    {
        $options = json_decode($options, true);
        return [
            'amount' => isset($options['amount']) ? $options['amount'] : 0,
            'fields' => isset($options['fields']) ? $options['fields'] : [],
        ];
    }

    public function setMainBookLimitAttribute($options)
    {
        $this->attributes['main_book_limit'] = json_encode([
            'amount' => isset($options['amount']) ? $options['amount'] : 0,
            'fields' => isset($options['fields']) ? $options['fields'] : [],
        ]);
    }

    public function getMainListLimitAttribute($options)
    {
        $options = json_decode($options, true);
        return [
            'amount' => isset($options['amount']) ? $options['amount'] : 0,
            'fields' => isset($options['fields']) ? $options['fields'] : [],
        ];
    }

    public function setMainListLimitAttribute($options)
    {
        $this->attributes['main_list_limit'] = json_encode([
            'amount' => isset($options['amount']) ? $options['amount'] : 0,
            'fields' => isset($options['fields']) ? $options['fields'] : [],
        ]);
    }

    public function getConsentAttribute($value)
    {
        $value = json_decode($value, true);
        return [
            'content' => isset($value['content']) ? $value['content'] : NULL,
            'precaution' => isset($value['precaution']) ? $value['precaution'] : NULL
        ];
    }

    public function setConsentAttribute($value)
    {
        $this->attributes['consent'] = json_encode([
            'content' => isset($value['content']) ? $value['content'] : NULL,
            'precaution' => isset($value['precaution']) ? $value['precaution'] : NULL
        ]);
    }

    public function getDueAttribute($time)
    {
        $time = json_decode($time, true);

        return isset($time) ? $time : ['start' => NULL, 'close' => NULL];
    }

    public function setDueAttribute($time)
    {
        $this->attributes['due'] = json_encode([
            'start' => isset($time['start']) ? $time['start'] : NULL,
            'close' => isset($time['close']) ? $time['close'] : NULL
        ]);
    }
}
