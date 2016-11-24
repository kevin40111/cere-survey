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

    public function getClassAttribute()
    {
        return self::class;
    }

}
