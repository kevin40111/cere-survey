<?php

namespace Cere\Survey\Eloquent;

use Eloquent;

class ExtendRule extends Eloquent {

    protected $connection = 'survey';

    protected $table = 'survey_extend_rule';

    public $timestamps = false;

    protected $fillable = array('book_id', 'rule');


    public function getRuleAttribute($rule)
    {
        $rule = json_decode($rule, true);
        return [
            'fieldsLimit' => isset($rule['fieldsLimit']) ? $rule['fieldsLimit'] : NULL,
            'columnsLimit' => isset($rule['columnsLimit']) ? $rule['columnsLimit'] : NULL,
            'fields' => isset($rule['fields']) ? $rule['fields'] : NULL,
            'conditionColumn_id' => isset($rule['conditionColumn_id']) ? $rule['conditionColumn_id'] : NULL,
        ];
    }

    public function setRuleAttribute($value)
    {
        $this->attributes['rule'] = json_encode([
            'fieldsLimit' => isset($value['fieldsLimit']) ? $value['fieldsLimit'] : NULL,
            'columnsLimit' => isset($value['columnsLimit']) ? $value['columnsLimit'] : NULL,
            'fields' => isset($value['fields']) ? $value['fields'] : NULL,
            'conditionColumn_id' => isset($value['conditionColumn_id']) ? $value['conditionColumn_id'] : NULL,
        ]);
    }

}