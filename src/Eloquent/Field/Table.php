<?php

namespace Cere\Survey\Eloquent\Field;

use Eloquent;

class Table extends Eloquent
{
    protected $table = 'row_tables';

    public $timestamps = true;

    protected $fillable = ['database', 'name', 'lock', 'builded_at', 'construct_at'];

    protected $attributes = ['lock' => false];

    public function getLockAttribute($value)
    {
        return (boolean)$value;
    }

    public function columns()
    {
        return $this->hasMany(Field::class, 'table_id', 'id');
    }

    public function sheet()
    {
        return $this->belongsTo(Sheet::class, 'sheet_id', 'id');
    }
}
