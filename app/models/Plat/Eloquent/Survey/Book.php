<?php

namespace Plat\Eloquent\Survey;

use Eloquent;

class Book extends Eloquent {

    use \Plat\Survey\Tree;

    protected $table = 'file_book';

    public $timestamps = false;

    protected $fillable = array('file_id', 'title');

    protected $appends = ['class'];

    public function childrenNodes()
    {
        return $this->morphMany('Plat\Eloquent\Survey\Node', 'parent');
    }

    // public function nodes()
    // {
    //     return $this->hasMany('Plat\Eloquent\Survey\Node', 'book_id', 'id');
    // }

    public function getClassAttribute()
    {
        return \Plat\Eloquent\Survey\Book::class;
    }

    public function getRewriteAttribute($value)
    {
        return (bool)$value;
    }

}
