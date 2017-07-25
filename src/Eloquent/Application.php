<?php

namespace Cere\Survey\Eloquent;

use Eloquent;
use Auth;

class Application extends Eloquent {

    use \SoftDeletingTrait;

    protected $table = 'survey_application';

    public $timestamps = true;

    protected $fillable = array('book_id', 'member_id', 'extension', 'reject', 'ext_book_id', 'updated_at', 'created_at', 'deleted_at', 'deleted_by');

    protected $attributes = ['extension' => false, 'reject' => false];

    public function book()
    {
        return $this->belongsTo('Cere\Survey\Eloquent\Book', 'book_id', 'id');
    }

    public function appliedOptions()
    {
        return $this->belongsToMany('Cere\Survey\Eloquent\ApplicableOption', 'survey_applied_options', 'application_id', 'applicable_option_id');
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

}
