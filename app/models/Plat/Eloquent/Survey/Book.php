<?php

namespace Plat\Eloquent\Survey;

use Eloquent;

class Book extends Eloquent {

    use \Plat\Survey\Tree;

    protected $table = 'file_book';

    public $timestamps = false;

    protected $fillable = array('file_id', 'title');

    protected $appends = ['class', 'types'];

    public function childrenNodes()
    {
        return $this->morphMany('Plat\Eloquent\Survey\Node', 'parent');
    }

    public function getClassAttribute()
    {
        return self::class;
    }

    protected $types = [
        'explain'  => ['name' => 'explain',  'editor' => ['title' => true, 'questions' => ['amount' => 0], 'answers' => 0], 'title' =>'說明文字',   'icon' =>'info-outline'],
        'select'   => ['name' => 'select',   'editor' => ['title' => false, 'questions' => ['amount' => 1], 'answers' => 100], 'title' =>'下拉式選單', 'icon' =>'arrow-drop-down-circle'],
        'radio'    => ['name' => 'radio',    'editor' => ['title' => false, 'questions' => ['amount' => 1], 'answers' => 20], 'title' =>'單選題',     'icon' =>'radio-button-checked'],
        'checkbox' => ['name' => 'checkbox', 'editor' => ['title' => true, 'questions' => ['amount' => 20, 'childrens' => true], 'answers' => 0], 'title' =>'複選題',     'icon' =>'check-box'],
        'scale'    => ['name' => 'scale',    'editor' => ['title' => true, 'questions' => ['amount' => 20], 'answers' => 10], 'title' =>'量表題',     'icon' =>'list'],
        'text'     => ['name' => 'text',     'editor' => ['title' => true, 'questions' => ['amount' => 20], 'answers' => 0], 'title' =>'文字填答',   'icon' =>'mode-edit'],
        'list'     => ['name' => 'list',     'editor' => ['title' => true, 'questions' => ['amount' => 0], 'answers' => 0], 'title' =>'題組',       'icon' =>'sitemap', 'disabled' => true],
        'textarea' => ['name' => 'textarea', 'editor' => ['title' => true, 'questions' => ['amount' => 0], 'answers' => 0], 'title' =>'文字欄位',   'disabled' => true],
        'table'    => ['name' => 'table',    'editor' => ['title' => true, 'questions' => ['amount' => 0], 'answers' => 0], 'title' =>'表格',       'disabled' => true],
        'jump'     => ['name' => 'jump',     'editor' => ['title' => true, 'questions' => ['amount' => 0], 'answers' => 0], 'title' =>'開啟題本',   'type' =>'rule'],
        'page'     => ['name' => 'page',     'editor' => ['title' => false, 'questions' => ['amount' => 0], 'answers' => 0], 'title' =>'頁',   'icon' =>'insert-drive-file', 'disabled' => true],
    ];

    public function getTypesAttribute()
    {
        return $this->types;
    }

}
