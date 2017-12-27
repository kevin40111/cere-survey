<?php

namespace Cere\Survey\Eloquent\Field;

use Eloquent;
use Cere\Survey\Eloquent as SurveyORM;

class Field extends Eloquent {

    use \Cere\Survey\Tree;

    protected $table = 'survey_fields';

    protected $connection = 'survey';

    public $timestamps = true;

    protected $fillable = array('name', 'title', 'rules', 'unique', 'encrypt', 'isnull', 'readonly', 'previous_id');

    protected $attributes = ['title' => '', 'name' => '', 'unique' => false, 'encrypt' => false, 'isnull' => false, 'readonly' => false];

    protected $appends = ['class', 'relation'];

    function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        // $this->deleting(function($model) {
        //     return $model->inTable->update(['construct_at' => \Carbon\Carbon::now()->toDateTimeString()]);
        // });

        // $this->saving(function($model) {
        //     $rules_updated = $model->isDirty('rules') && $model->inTable->update(['construct_at' => \Carbon\Carbon::now()->toDateTimeString()]);
        //     return $model->isDirty('rules') ? $rules_updated : true;
        // });
    }

    public function getClassAttribute()
    {
        return self::class;
    }

    public function inTable() {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function answers()
    {
        return $this->hasMany(SurveyORM\Answer::class, 'column_id', 'id');
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

    /*
     * From Question
     */
    public function node()
    {
        return $this->hasOne(SurveyORM\Node::class, 'id', 'node_id');
    }

    public function next()
    {
        return $this->hasOne(self::class, 'previous_id', 'id');
    }

    public function previous()
    {
        return $this->hasOne(self::class, 'id', 'previous_id');
    }

    public function childrenNodes()
    {
        return $this->morphMany(SurveyORM\Node::class, 'parent');
    }

    public function getRelationAttribute()
    {
        return 'questions';
    }

    public function getRequiredAttribute($value)
    {
        return (bool)$value;
    }

    public function rule()
    {
        return $this->morphOne(SurveyORM\Rule::class, 'effect');
    }
}
