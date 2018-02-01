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

    protected $fillable = array('extension', 'reject', 'fields', 'updated_at', 'created_at', 'deleted_at', 'deleted_by');

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
}
