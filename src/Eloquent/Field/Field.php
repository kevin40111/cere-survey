<?php

namespace Cere\Survey\Eloquent\Field;

use Eloquent;
use Cere\Survey\Eloquent as SurveyORM;

class Field extends Eloquent
{
    protected $connection = 'survey';

    protected $table = 'survey_fields';

    public $timestamps = true;

    protected $fillable = ['name', 'title', 'rules', 'unique', 'encrypt', 'isnull', 'readonly'];

    protected $attributes = ['title' => '', 'name' => '', 'unique' => false, 'encrypt' => false, 'isnull' => false, 'readonly' => false];

    protected $appends = ['class'];

    public function getClassAttribute()
    {
        return self::class;
    }

    public function inTable()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function answers()
    {
        return $this->morphMany(SurveyORM\Answer::class, 'belong');
    }

    public function skip()
    {
        return $this->hasOne('Row\Skip', 'column_id', 'id');
    }

    public function getUniqueAttribute($value)
    {
        return (boolean)$value;
    }

    public function getEncryptAttribute($value)
    {
        return (boolean)$value;
    }

    public function getIsnullAttribute($value)
    {
        return (boolean)$value;
    }

    public function getReadonlyAttribute($value)
    {
        return (boolean)$value;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }

    public function setUniqueAttribute($value)
    {
        $this->attributes['unique'] = isset($value) ? $value : false;
    }

    public function setEncryptAttribute($value)
    {
        $this->attributes['encrypt'] = isset($value) ? $value : false;
    }

    public function setIsnullAttribute($value)
    {
        $this->attributes['isnull'] = isset($value) ? $value : false;
    }

    public function setReadonlyAttribute($value)
    {
        $this->attributes['readonly'] = isset($value) ? $value : false;
    }
}
