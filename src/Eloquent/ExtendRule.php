<?php

namespace Cere\Survey\Eloquent;

use Eloquent;

class ExtendRule extends Eloquent {

    protected $connection = 'survey';

    protected $table = 'survey_extend_rule';

    public $timestamps = false;

    protected $fillable = ['book_id', 'rule'];

    public function getRuleAttribute($rule)
    {
        $rule = json_decode($rule, true);
        return [
            'fieldsLimit' => isset($rule['fieldsLimit']) ? $rule['fieldsLimit'] : 0,
            'columnsLimit' => isset($rule['columnsLimit']) ? $rule['columnsLimit'] : 0,
            'fields' => isset($rule['fields']) ? $rule['fields'] : [],
            'conditionColumn_id' => isset($rule['conditionColumn_id']) ? $rule['conditionColumn_id'] : NULL,
        ];
    }

    public function setRuleAttribute($value)
    {
        $this->attributes['rule'] = json_encode([
            'fieldsLimit' => isset($value['fieldsLimit']) ? $value['fieldsLimit'] : 0,
            'columnsLimit' => isset($value['columnsLimit']) ? $value['columnsLimit'] : 0,
            'fields' => isset($value['fields']) ? $value['fields'] : [],
            'conditionColumn_id' => isset($value['conditionColumn_id']) ? $value['conditionColumn_id'] : NULL,
        ]);
    }
}
