<?php

namespace Cere\Survey\Eloquent\Extend;

use Eloquent;

class Hook extends Eloquent {

    protected $connection = 'survey';

    protected $table = 'survey_extend_hooks';

    public $timestamps = false;

    protected $fillable = ['options'];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function getOptionsAttribute($options)
    {
        $options = json_decode($options, true);
        return [
            'fieldsLimit' => isset($options['fieldsLimit']) ? $options['fieldsLimit'] : 0,
            'columnsLimit' => isset($options['columnsLimit']) ? $options['columnsLimit'] : 0,
            'fields' => isset($options['fields']) ? $options['fields'] : [],
        ];
    }

    public function setOptionsAttribute($options)
    {
        $this->attributes['options'] = json_encode([
            'fieldsLimit' => isset($options['fieldsLimit']) ? $options['fieldsLimit'] : 0,
            'columnsLimit' => isset($options['columnsLimit']) ? $options['columnsLimit'] : 0,
            'fields' => isset($options['fields']) ? $options['fields'] : [],
        ]);
    }

    public function getConsentAttribute($value)
    {
        $value = json_decode($value, true);
        return [
            'content' => isset($value['content']) ? $value['content'] : NULL,
            'precaution' => isset($value['precaution']) ? $value['precaution'] : NULL
        ];
    }

    public function setConsentAttribute($value)
    {
        $this->attributes['consent'] = json_encode([
            'content' => isset($value['content']) ? $value['content'] : NULL,
            'precaution' => isset($value['precaution']) ? $value['precaution'] : NULL
        ]);
    }
}
