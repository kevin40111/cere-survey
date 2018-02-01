<?php

namespace Cere\Survey\Eloquent\Extend;

use Eloquent;
use Auth;
use Cere\Survey\Eloquent\Field\Field;
use Cere\Survey\Eloquent\Extend\Option;

class Application extends Eloquent {

    use \SoftDeletingTrait;

    protected $connection = 'survey';

    protected $table = 'survey_extend_applications';

    public $timestamps = true;

    protected $fillable = array('book_id', 'member_id', 'extension', 'reject', 'ext_book_id', 'updated_at', 'fields', 'created_at', 'deleted_at', 'deleted_by');

    protected $attributes = ['extension' => false, 'reject' => false];

    public function book()
    {
        return $this->belongsTo('Cere\Survey\Eloquent\Book', 'book_id', 'id');
    }

    public function Option()
    {
        return $this->belongsTo(Option::class);
    }

    public function appliedFields()
    {
        return $this->belongsToMany(Field::class, 'survey_applied_options');
    }

    public function members()
    {
        return $this->belongsTo('Plat\Member', 'member_id', 'id');
    }

    public function scopeOfMe($query)
    {
        return $query->where('member_id', Auth::user()->members()->Logined()->orderBy('logined_at', 'desc')->first()->id);
    }

    public function getExtensionAttribute($value)
    {
        return (boolean)$value;
    }

    public function getRejectAttribute($value)
    {
        return (boolean)$value;
    }

    public function getFieldsAttribute($rule)
    {
        $rule = json_decode($rule, true);
        return [
            'fields' => isset($rule['fields']) ? $rule['fields'] : [],
        ];
    }

    public function setFieldsAttribute($value)
    {
        $this->attributes['rule'] = json_encode([
            'fields' => isset($value['fields']) ? $value['fields'] : [],
        ]);
    }

    public function reasons()
    {
        return $this->hasMany('Cere\Survey\Eloquent\Extend\Reason', 'extend_application_id');
    }
}
