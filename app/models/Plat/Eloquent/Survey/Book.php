<?php

namespace Plat\Eloquent\Survey;

use Eloquent;

class Book extends Eloquent {

    protected $table = 'file_book';

    public $timestamps = false;

    protected $fillable = array('file_id', 'title');

    protected $guarded = array('id');

    protected $appends = ['class'];

    public function wave()
    {
        return $this->hasOne('Plat\Eloquent\Survey\Wave', 'id', 'wave_id');
    }

    public function questions()
    {
        return $this->hasMany('Plat\Eloquent\Survey\Question', 'book_id', 'id');
    }

    public function getClassAttribute()
    {
        return \Plat\Survey\Book::class;
    }

    public function getRewriteAttribute($value)
    {
        return (bool)$value;
    }

}
