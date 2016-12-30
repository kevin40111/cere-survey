<?php

namespace Plat\Eloquent\Survey;

use Eloquent;
use Auth;

class Application extends Eloquent {

    // use SoftDeletingTrait;

    protected $table = 'survey_application';

    public $timestamps = true;

    // protected $dates = ['deleted_at'];

    protected $fillable = array('book_id', 'member_id', 'extension', 'ext_book_id', 'updated_at', 'created_at', 'deleted_at', 'deleted_by');

    public function book()
    {
        return $this->belongsTo('Plat\Eloquent\Survey\Book', 'book_id', 'id');
    }

    public function appliedOptions()
    {
        return $this->belongsToMany('Plat\Eloquent\Survey\ApplicableOption', 'survey_applied_options', 'application_id', 'applicable_option_id');
    }

    public function members()
    {
        return $this->belongsTo('Plat\Member', 'member_id', 'id');
    }

    public function scopeOfMe($query)
    {
        //Auth::user()->members()->Logined()->orderBy('logined_at', 'desc')->first();
        return $query->where('member_id', Auth::user()->members()->Logined()->orderBy('logined_at', 'desc')->first()->id);
    }

}
