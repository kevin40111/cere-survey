<?php

namespace Cere\Survey\Eloquent\Extend;

use Eloquent;
use Auth;
use Cere\Survey\Eloquent\Field\Field;
use Cere\Survey\Eloquent as SurveyORM;
use Plat\Member;

class Application extends Eloquent {

    use \SoftDeletingTrait;

    protected $connection = 'survey';

    protected $table = 'survey_extend_applications';

    public $timestamps = true;

    protected $fillable = array('book_id', 'extension', 'reject', 'fields', 'updated_at', 'step', 'created_at', 'deleted_at', 'deleted_by');

    protected $attributes = ['extension' => false, 'reject' => false];

    public function book()
    {
        return $this->belongsTo(SurveyORM\Book::class);
    }

    public function hook()
    {
        return $this->belongsTo(Hook::class);
    }

    public function members()
    {
        return $this->belongsTo(Member::class);
    }

    public function reasons()
    {
        return $this->hasMany('Cere\Survey\Eloquent\Extend\Reason', 'extend_application_id');
    }

    public function getExtensionAttribute($value)
    {
        return (boolean)$value;
    }

    public function getRejectAttribute($value)
    {
        return (boolean)$value;
    }

    public function getFieldsAttribute($fields)
    {
        $fields = json_decode($fields, true);
        return isset($fields) ? $fields : [];
    }

    public function setFieldsAttribute($fields)
    {
        $this->attributes['fields'] = json_encode(isset($fields) ? $fields : []);
    }
}
