<?php

namespace Cere\Survey\Eloquent;

use Eloquent;
use Cere\Survey\Eloquent\Field\Sheet;
use Cere\Survey\Eloquent\Field\Field;
use Carbon\Carbon;

class Book extends Eloquent {

    use \Cere\Survey\Tree;

    protected $connection = 'survey';

    protected $table = 'survey_book';

    public $timestamps = false;

    protected $fillable = array('file_id', 'title', 'lock', 'loginRow_id', 'auth', 'footer');

    protected $attributes = ['lock' => false];

    protected $appends = ['class', 'types'];

    public function sheet()
    {
        return $this->belongsTo(Sheet::class);
    }

    public function childrenNodes()
    {
        return $this->morphMany('Cere\Survey\Eloquent\Node', 'parent');
    }

    public function getClassAttribute()
    {
        return self::class;
    }

    protected $types = [
        'explain'  => ['name' => 'explain',  'editor' => ['enter' => false, 'title' => '填答說明', 'questions' => ['amount' => 0, 'text' =>''], 'answers' => 0], 'title' =>'說明文字',   'icon' =>'info-outline'],
        'select'   => ['name' => 'select',   'editor' => ['enter' => false, 'title' => false, 'questions' => ['amount' => 1, 'text' =>'問題'], 'answers' => 100, 'answerChilderns' => true], 'title' =>'下拉式選單', 'icon' =>'arrow-drop-down-circle'],
        'radio'    => ['name' => 'radio',    'editor' => ['enter' => false, 'title' => false, 'questions' => ['amount' => 1, 'text' =>'問題'], 'answers' => 20, 'answerChilderns' => true], 'title' =>'單選題',     'icon' =>'radio-button-checked'],
        'checkbox' => ['name' => 'checkbox', 'editor' => ['enter' => false, 'title' => '填答說明', 'questions' => ['amount' => 20, 'childrens' => true, 'text' =>'選項'], 'answers' => 0], 'title' =>'複選題',     'icon' =>'check-box'],
        'scale'    => ['name' => 'scale',    'editor' => ['enter' => false, 'title' => '請輸入問題描述', 'questions' => ['amount' => 20, 'text' =>'問題'], 'answers' => 10], 'title' =>'量表題',     'icon' =>'scale'],
        'text'     => ['name' => 'text',     'editor' => ['enter' => false, 'title' => '請輸入問題描述', 'questions' => ['amount' => 20, 'text' =>'問題'], 'answers' => 0], 'title' =>'文字填答',   'icon' =>'mode-edit'],
        'number'   => ['name' => 'number',   'editor' => ['enter' => false, 'title' => '請輸入問題描述', 'questions' => ['amount' => 1, 'text' =>'問題'], 'answers' => 0], 'title' =>'數字',   'icon' =>'mode-edit'],
        'list'     => ['name' => 'list',     'editor' => ['enter' => false, 'title' => '填答說明', 'questions' => ['amount' => 0, 'text' =>'問題'], 'answers' => 0], 'title' =>'題組',       'icon' =>'sitemap', 'disabled' => true],
        'textarea' => ['name' => 'textarea', 'editor' => ['enter' => false, 'title' => '填答說明', 'questions' => ['amount' => 0, 'text' =>'問題'], 'answers' => 0], 'title' =>'文字欄位',   'disabled' => true],
        'table'    => ['name' => 'table',    'editor' => ['enter' => false, 'title' => '填答說明', 'questions' => ['amount' => 0, 'text' =>'問題'], 'answers' => 0], 'title' =>'表格',       'disabled' => true],
        'jump'     => ['name' => 'jump',     'editor' => ['enter' => false, 'title' => true, 'questions' => ['amount' => 0], 'answers' => 0, 'text' =>'問題'], 'title' =>'開啟題本',   'type' =>'rule',  'disabled' => true],
        'page'     => ['name' => 'page',     'editor' => ['enter' => true, 'title' => '這裡輸入的文字不會顯示在問卷中', 'questions' => ['amount' => 0, 'text' =>'問題'], 'answers' => 0], 'title' =>'頁',   'icon' =>'insert-drive-file', 'disabled' => true],
        'gear'     => ['name' => 'gear',     'editor' => ['enter' => false, 'title' => '聯動資料上傳', 'questions' => ['amount' => 1, 'childrens' => true, 'createQuestion' => false, 'text' =>'問題'], 'createAnswer'=> false, 'answers' => 40, 'answerChilderns' => false, 'uploadFile' => true,],'title' =>'連動下拉式選單','icon' =>'arrow-drop-down'],
    ];

    public function getTypesAttribute()
    {
        return $this->types;
    }

    public function getLockAttribute($value)
    {
        return (boolean)$value;
    }

    public function getAuthAttribute($value)
    {
        $auth = $value ? json_decode($value, true) : ['fields' => []];
        return [
            'fieldFile_id' => isset($auth['fieldFile_id']) ? $auth['fieldFile_id'] : NULL,
            'inputFields' => array_keys($auth['fields']),
            'validFields' => array_keys(array_filter($auth['fields'], function($field) { return $field['valid']; })),
            'start_at' => isset($auth['start_at']) ? Carbon::parse($auth['start_at']) : Carbon::minValue(),
            'close_at' => isset($auth['close_at']) ? Carbon::parse($auth['close_at']) : Carbon::maxValue(),
        ];
    }

    public function setAuthAttribute($auth)
    {
        $fields = [];
        foreach ($auth['fields'] as $field) {
            $fields[$field['id']] = ['valid' => $field['isValid']];
        }
        $this->attributes['auth'] = json_encode([
            'fieldFile_id' => $auth['fieldFile_id'],
            'fields' => $fields,
            'start_at' => isset($auth['start_at']) ? Carbon::parse($auth['start_at'])->tz('Asia/Taipei')->toDateTimeString() : NULL,
            'close_at' => isset($auth['close_at']) ? Carbon::parse($auth['close_at'])->tz('Asia/Taipei')->toDateTimeString() : NULL,
        ]);
    }

    public function applicableOptions()
    {
        return $this->hasMany('Cere\Survey\Eloquent\ApplicableOption', 'book_id', 'id');
    }

    public function applications()
    {
        return $this->hasMany('Cere\Survey\Eloquent\Application', 'book_id', 'id');
    }

    public function optionFields()
    {
        return $this->morphedByMany(Field::class, 'survey_applicable_option');
    }

    public function file()
    {
        return $this->belongsTo('Files', 'file_id', 'id');
    }

    public function rule()
    {
        return $this->morphOne('Cere\Survey\Eloquent\Rule', 'effect');
    }
}
