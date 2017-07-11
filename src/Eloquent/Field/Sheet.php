<?php

namespace Plat\Eloquent\Field;

use Eloquent;

class Sheet extends Eloquent {

    protected $table = 'row_sheets';

    public $timestamps = true;

    protected $fillable = array('title', 'editable', 'fillable');

    public function getEditableAttribute($value) {
        return (boolean)$value;
    }

    public function getFillableAttribute($value) {
        return (boolean)$value;
    }

    public function tables() {
        return $this->hasMany(Table::class, 'sheet_id', 'id');
    }

    public function file()
    {
        return $this->belongsTo('Files', 'file_id', 'id');
    }
}
