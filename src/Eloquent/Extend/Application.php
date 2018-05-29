<?php

namespace Cere\Survey\Eloquent\Extend;

use Eloquent;
use Cere\Survey\Eloquent as SurveyORM;
use Plat\Member;
use Cere\Survey\Eloquent\Rule\Ruler;

class Application extends Ruler
{
    use \SoftDeletingTrait;

    protected $connection = 'survey';

    protected $table = 'survey_extend_applications';

    public $timestamps = true;

    protected $fillable = ['book_id', 'member_id', 'agree', 'status', 'fields', 'updated_at', 'step', 'created_at', 'deleted_at', 'deleted_by', 'individual_status'];

    protected $attributes = ['agree' => false, 'status' => 0];

    public function book()
    {
        return $this->belongsTo(SurveyORM\Book::class);
    }

    public function hook()
    {
        return $this->belongsTo(Hook::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function messages()
    {
        return $this->morphMany(SurveyORM\Message::class, 'target');
    }

    public function getAgreeAttribute($value)
    {
        return (boolean)$value;
    }

    public function getStatusAttribute($value)
    {
        return (integer)$value;
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

    public function getIndividualStatusAttribute($status)
    {
        $status = json_decode($status, true);
        return isset($status) ? $status : [
            'apply' => 0,
            'book' => 0
        ];
    }

    public function setIndividualStatusAttribute($status)
    {
        $this->attributes['individual_status'] = json_encode(isset($status) ? $status : [
            'apply' => 0,
            'book' => 0
        ]);
    }
}
