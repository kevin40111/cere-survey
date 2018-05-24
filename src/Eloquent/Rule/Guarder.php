<?php

namespace Cere\Survey\Eloquent\Rule;

use Cere\Survey\Eloquent\Node;

class Guarder extends Ruler
{
    protected $connection = 'survey';

    protected $table = 'survey_rule_guarders';

    public $timestamps = false;

    protected $fillable = ['method', 'priority'];

    public function target()
    {
        return $this->morphTo();
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function($guarder) {

            $guarder->operations->each(function ($operation) {
                $operation->delete();
            });

        });
    }
}
