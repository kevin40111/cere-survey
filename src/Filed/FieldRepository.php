<?php

namespace Cere\Survey\Field;

use Carbon\Carbon;
use Schema;
use DB;
use Auth;
use Cere\Survey\Eloquent\Field\Table;
use Cere\Survey\Eloquent\Field\Field;

class FieldRepository
{
    public $field;

    public static $rules = [
        'gender'      => ['sort' => 1,  'type' => 'tinyInteger',             'title' => '性別: 1.男 2.女',               'validator' => 'in:1,2', 'editor' => 'menu'],
        'gender_id'   => ['sort' => 2,  'type' => 'tinyInteger',             'title' => '性別: 1.男 2.女(身分證第2碼)',  'validator' => 'in:1,2'],
        'bool'        => ['sort' => 3,  'type' => 'boolean',                 'title' => '是(1)與否(0)',                  'validator' => 'boolean', 'editor' => 'menu'],
        'stdidnumber' => ['sort' => 4,  'type' => 'string',   'size' => 10,  'title' => '身分證',                        'function' => 'stdidnumber'],
        'email'       => ['sort' => 5,  'type' => 'string',   'size' => 80,  'title' => '信箱',                          'validator' => 'email'],
        'date_six'    => ['sort' => 6,  'type' => 'string',   'size' => 6,   'title' => '日期(yymmdd)',                  'validator' => ['regex:/^([0-9][0-9])(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])$/']],
        'order'       => ['sort' => 7,  'type' => 'string',   'size' => 3,   'title' => '順序(1-99,-7)',                 'validator' => ['regex:/^([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|-7)$/']],
        'score'       => ['sort' => 8,  'type' => 'string',   'size' => 3,   'title' => '成績(A++,A+,A,B++,B+,B,C,-7)',  'validator' => 'in:A++,A+,A,B++,B+,B,C,-7'],
        'score_six'   => ['sort' => 9,  'type' => 'string',   'size' => 2,   'title' => '成績(0~6,-7)',                  'validator' => 'in:0,1,2,3,4,5,6,-7'],
        'phone'       => ['sort' => 10, 'type' => 'string',   'size' => 20,  'title' => '手機',                          'regex' => '/^\w+$/'],
        'tel'         => ['sort' => 11, 'type' => 'string',   'size' => 20,  'title' => '電話',                          'regex' => '/^\w+$/'],
        'address'     => ['sort' => 12, 'type' => 'string',   'size' => 80,  'title' => '地址'],
        'schid_104'   => ['sort' => 13, 'type' => 'string',   'size' => 6,   'title' => '高中職學校代碼(104)', 'function' => 'schid_104'],
        'schid_105'   => ['sort' => 14, 'type' => 'string',   'size' => 6,   'title' => '高中職學校代碼(105)', 'function' => 'schid_105'],
        'depcode_104' => ['sort' => 15, 'type' => 'string',   'size' => 6,   'title' => '高中職科別代碼(104)', 'function' => 'depcode_104'],
        'depcode_105' => ['sort' => 16, 'type' => 'string',   'size' => 6,   'title' => '高中職科別代碼(105)', 'function' => 'depcode_105'],
        'text'        => ['sort' => 17, 'type' => 'string',   'size' => 50,  'title' => '文字(50字以內)'],
        'nvarchar'    => ['sort' => 18, 'type' => 'string',   'size' => 500, 'title' => '文字(500字以內)'],
        'int'         => ['sort' => 19, 'type' => 'integer',                 'title' => '整數',                         'validator' => 'integer'],
        'float'       => ['sort' => 20, 'type' => 'string',   'size' => 80,  'title' => '小數',                         'validator' => ['regex:/^([0-9]|[1-9][0-9]{1,40})(\\.[0-9]{1,39})?$/']],
        'year_four'   => ['sort' => 21, 'type' => 'string',   'size' => 4,   'title' => '西元年(yyyy)',                 'validator' => ['regex:/^(19[0-9]{2})$/']],
        'j_in_city'   => ['sort' => 22, 'type' => 'string',   'size' => 6,   'title' => '縣市所屬國中',                 'function'  => 'junior_schools_in_city'],
        //師培
        'tted_sch'         => ['sort' => 23, 'type' => 'string',   'size' => 10,   'title' => 'TTED大專院校學校代碼',      'function' => 'tted_sch'],
        'tted_depcode_103' => ['sort' => 24, 'type' => 'string',   'size' => 6,   'title' => 'TTED大專院校系所代碼103年', 'function' => 'tted_depcode_103'],
        'tted_depcode_104' => ['sort' => 25, 'type' => 'string',   'size' => 6,   'title' => 'TTED大專院校系所代碼104年', 'function' => 'tted_depcode_104'],
        'tted_depcode_105' => ['sort' => 25, 'type' => 'string',   'size' => 6,   'title' => 'TTED大專院校系所代碼105年', 'function' => 'tted_depcode_105'],
        'stdschoolstage'   => ['sort' => 26, 'type' => 'tinyInteger',             'title' => 'TTED教育階段',              'validator' => 'in:1,2,3'],
        'schoolsys'        => ['sort' => 27, 'type' => 'tinyInteger',             'title' => 'TTED學制別',                'validator' => 'in:1,2'],
        'program'          => ['sort' => 28, 'type' => 'tinyInteger',             'title' => 'TTED修課資格',              'validator' => 'in:0,1,2,3'],
        'govexp'           => ['sort' => 29, 'type' => 'tinyInteger',             'title' => 'TTED公費生',                'validator' => 'in:0,1,2,3,4'],
        'other'            => ['sort' => 30, 'type' => 'tinyInteger',             'title' => 'TTED外加名額',              'validator' => 'in:0,1,2,3,4,5,6,7,8,9,10'],
        'stdyear'          => ['sort' => 31, 'type' => 'string',   'size' => 1,   'title' => 'TTED年級',                  'validator' => 'in:1,2,3,4,5,6,7'],
        'string_dot'       => ['sort' => 32, 'type' => 'string',   'size' => 100, 'title' => '文字(逗號分隔)',            'regex'     => '/^[\x{0080}-\x{00FF},]+$/'],
        'float_hundred'    => ['sort' => 22, 'type' => 'string',   'size' => 8,   'title' => '小數(1-100,-7)',            'validator' => ['regex:/^(([0-9]|[1-9][0-9])(\\.[0-9]{1,5})?|100|-7)$/']],
        'yyy'              => ['sort' => 33, 'type' => 'string',   'size' => 3,   'title' => '民國年',                    'validator' => ['regex:/^([1-9]|[1-9][0-9]|[1][0-1][0-9])$/']],
        'menu'             => ['sort' => 34, 'type' => 'tinyInteger',             'title' => '選單',                      'menu' => '', 'editor' => 'menu'],
        'counties'         => ['sort' => 35, 'type' => 'string',   'size' => 2,   'title' => '縣市(六都改制)',             'function'  => 'counties', 'editor' => 'menu'],
        'gateway'          => ['sort' => 36, 'type' => 'tinyInteger',             'title' => '師資生核定培育管道',          'validator' => 'in:0,1,2', 'editor' => 'menu'],
    ];

    protected static $database = 'rows';

    protected static $checkDatabase = 'rows_check';

    protected $import = [];

    protected $checkTable;

    protected $fullDataTable;

    protected $fullCheckTable;

    function __construct($field)
    {
        $this->field = $field;
        $this->checkTable = $this->field->name . str_random(50);
        $this->fullDataTable = $this->field->database . '.dbo.' . $this->field->name;
        $this->fullCheckTable = self::$checkDatabase . '.dbo.' . $this->checkTable;
    }

    public static function target($target)
    {
        return new self($target);
    }

    public static function create()
    {
        $field = new Table(['database' => self::$database, 'name' => self::generate_table(), 'lock' => false, 'construct_at' => Carbon::now()->toDateTimeString()]);

        return new self($field);
    }

    public function init()
    {
        $this->field->columns->each(function ($column) {
            if (isset(self::$rules[$column->rules]['editor']) && self::$rules[$column->rules]['editor'] == 'menu') {
                $answers = $this->setAnswers($column);
            }
        });

        $this->construct();

        return $this;
    }

    /**
     * Build table if sheet was changed.
     */
    public function construct()
    {
        if (! isset($this->field->builded_at) || ! $this->exists() || Carbon::parse($this->field->builded_at)->diffInSeconds(new Carbon($this->field->construct_at), false) > 0) {
            $this->bulidDataTable();
        }
    }

    private static function generate_table()
    {
        return 'row_' . Carbon::now()->formatLocalized('%Y%m%d_%H%M%S') . '_' . strtolower(str_random(5));
    }

    public function import($rows)
    {
        $this->import['rows'] = $rows;

        $this->check_head();

        $this->check_repeat();

        $messages = $this->cleanRow();

        $inserts = array_filter($messages, function ($message) {
            return $message->pass;
        });

        DB::beginTransaction();

        $this->bulidCheckTable();

        foreach (array_chunk(array_map(function ($message) { return $message->row; }, $inserts), floor(2000/($this->field->columns->count()+1))) as $part) {
            DB::table($this->fullCheckTable)->insert($part);
        }

        $amounts = [];

        if ($this->field->columns->groupBy('unique')->has(1))
            $amounts['removed'] = $this->removeRowsInTemp();

        $amounts['created'] = $this->moveRowsFromTemp();

        $this->dropCheckTable();

        DB::commit();

        $this->field->update(['lock' => true]);

        return [$messages, $amounts];
    }

    private function bulidCheckTable()
    {
        $this->dropCheckTable();

        $this->bulid($this->fullCheckTable, function ($query) {
            $this->appendColumns($query);
            $query->integer('index');
        });
    }

    private function bulidDataTable()
    {
        $this->exists() && Schema::drop($this->fullDataTable);

        $this->bulid($this->fullDataTable, function ($query) {
            $this->appendColumns($query);
            $query->dateTime('updated_at');
            $query->dateTime('created_at');
            $query->dateTime('deleted_at')->nullable();
            $query->text('updated_by', 50);
            $query->text('created_by', 50);
            $query->integer('deleted_by')->nullable();
        });

        $this->field->update(['builded_at' => Carbon::now()->toDateTimeString()]);
    }

    private function bulid($table, $others)
    {
        Schema::create($table, $others);
    }

    private function appendColumns($query)
    {
        $query->increments('id');

        foreach ($this->field->columns as $column) {
            $this->column_bulid($query, 'C' . $column->id, $column->rules);
        }
    }

    private function dropCheckTable()
    {
        if ($this->tableExists(self::$checkDatabase, $this->checkTable)) {

            Schema::drop($this->fullCheckTable);

        }
    }

    private function column_bulid($query, $name, $rule_key, $indexs = [])
    {
        if (isset(self::$rules[$rule_key])) {
            $rule = self::$rules[$rule_key];
            $para = isset($rule['size']) ? [$name, $rule['size']] : [$name];
            call_user_func_array([$query, $rule['type']], $para);
            foreach ($indexs as $index) {
                $query->$index();
            }
        }
    }

    /**
     * Determine if table is exist.
     */
    private function exists()
    {
        return $this->tableExists($this->field->database, $this->field->name);
    }

    private function tableExists($database, $name)
    {
        return DB::table($database . '.INFORMATION_SCHEMA.COLUMNS')->where('TABLE_NAME', $name)->exists();
    }

    private function check_head()
    {
        $head = head($this->import['rows']);

        $checked_head = $this->field->columns->filter(function ($column) use ($head) {
            return !array_key_exists($column->name, $head ? $head : []);
        });

        if (!$checked_head->isEmpty()) {
            throw new ImportException(['head' => $checked_head]);
        }
    }

    private function check_repeat()
    {
        $columns_repeat = $this->field->columns->filter(function ($column) {

            return $column->unique;

        })->map(function ($column) {

            $cells = array_pluck($this->import['rows'], $column->name);

            $repeats = array_count_values(array_map('strval', $cells));

            foreach (array_keys($repeats, 1, true) as $key) {
                unset($repeats[$key]);
            }

            if (!empty($repeats)) {
                throw new ImportException(['repeat' => ['title' => $column->name, 'values' => $repeats]]);
            }
        });
    }

    public function cleanRow()
    {
        $index = 0;
        return array_map(function ($row) use (&$index) {

            $row_filted = array_filter(array_map('strval', $row), function ($value) { return $value != ''; });

            $message = (object)['pass' => false, 'limit' => false, 'empty' => empty($row_filted), 'updated' => false, 'exists' => [], 'errors' => [], 'row' => []];

            // skip if empty
            if ($message->empty)
                return $message;

            foreach ($this->field->columns as $column)
            {
                $value = $message->row['C' . $column->id] = isset($row[$column->name]) ? remove_space($row[$column->name]) : '';

                $skip = false;
                if ($column->skip && isset($message->row['C' . $column->skip->rules->by_column_id])) {
                    $skip = $message->row['C' . $column->skip->rules->by_column_id] == $column->skip->rules->value;
                }

                if (!$skip && (!$column->isnull || !empty($value))) {

                    $column_errors = $this->check_column($column, $value);

                    !empty($column_errors) && $message->errors[$column->id] = $column_errors;
                }
            }

            $message->pass = !$message->limit && empty($message->errors);

            $message->row['index'] = $index++;

            return $message;

        }, $this->import['rows']);
    }

    private function removeRowsInTemp()
    {
        $updates = $this->field->columns->map(function ($column) { return 'rows.C' . $column->id . '=checked.C' . $column->id; });

        $query_update = DB::table($this->fullDataTable . ' AS rows')
        ->leftJoin($this->fullCheckTable . ' AS checked', function ($join) {
            $this->field->columns->each(function ($column) use ($join) {
                if ($column->unique) {
                    $join->on('checked.C' . $column->id, '=', 'rows.C' . $column->id);
                }
            });
        })->whereNotNull('checked.id');

        $amount = DB::delete('DELETE rows ' . $query_update->toSql() . ' and rows.created_by = ' . Auth::user()->id);

        return $amount;
    }

    private function moveRowsFromTemp()
    {
        $checkeds = $this->field->columns->map(function ($column) { return 'checked.C' . $column->id; });
        $columns = $this->field->columns->map(function ($column) { return 'C' . $column->id; });

        $query_insert = DB::table($this->fullCheckTable . ' AS checked')->select(array_merge($checkeds->toArray(), [
            DB::raw('\'' . Auth::user()->id . '\''),
            DB::raw('\'' . Auth::user()->id . '\''),
            DB::raw('\'' . Carbon::now()->toDateTimeString() . '\''),
            DB::raw('\'' . Carbon::now()->toDateTimeString() . '\''),
        ]));

        $amount = $query_insert->count();

        $success = DB::insert('INSERT INTO ' .
            $this->fullDataTable . ' (' . implode(',', $columns->toArray()) . ', updated_by, created_by, updated_at, created_at) ' .
            $query_insert->toSql()
        );

        return $success ? $amount : 0;
    }

    private function check_row($row)
    {
        $errors = [];

        foreach ($this->field->columns as $column)
        {
            $value = isset($row['C' . $column->id]) ? remove_space($row['C' . $column->id]) : '';

            if (!$column->encrypt && (!$column->isnull || !empty($value)))
            {
                $column->menu = $column->answers->lists('value');

                $column_errors = $this->check_column($column, $value);

                !empty($column_errors) && $errors[$column->id] = $column_errors;
            }
        }

        return $errors;
    }

    /**
     * Get check columns errors.
     */
    private function check_column($column, $column_value)
    {
        $column_errors = [];

        check_empty($column_value, $column->title, $column_errors);

        if( empty( $column_errors ) )
        {
            $rules = self::$rules[$column->rules];
            if (isset($rules['regex']) && !preg_match($rules['regex'], $column_value)) {
                array_push($column_errors, $column->title . '格式錯誤');
            }
            if (isset($rules['validator'])) {
                $validator = \Validator::make([$column->id => $column_value], [$column->id => $rules['validator']]);
                $validator->fails() && array_push($column_errors, $column->title . '格式錯誤');
            }
            if (isset($rules['function'])) {
                call_user_func_array($this->checker($rules['function']), array($column_value, $column, &$column_errors));
            }

            if (!$column->unique && isset($rules['menu'])) {
                if (!in_array($column_value, $column->answers->lists('value'), true)) {
                    array_push($column_errors, $column->title . '未在選單中');
                }
            }
        }

        return $column_errors;
    }

    public function get_rows($query, $search)
    {
        $head = $this->field->columns->map(function ($column) { return 'C' . $column->id; })->toArray();

        if ($search) {
            $query->where('C' . $search['column_id'], $search['text']);
        }

        $query->whereNull('deleted_at')->select($head)->addSelect('id');

        $paginate = $query->paginate(15);

        $encrypts = $this->field->columns->filter(function ($column) { return $column->encrypt; });

        if (!$encrypts->isEmpty()) {
            $paginate->getCollection()->each(function ($row) use ($encrypts) {
                $this->setEncrypts($row, $encrypts);
            });
        }

        return $paginate;
    }

    public function updateRow($query, $row)
    {
        $row['errors'] = $this->check_row($row);

        if (empty($row['errors']))
        {
            $columns = $this->field->columns->filter(function ($column) {
                return !$column->encrypt;
            })->map(function ($column) {
                return 'C' . $column->id;
            })->toArray();

            $row['updated'] = $query->where('id', $row['id'])->update(array_only($row, $columns));
        }

        return $row;
    }

    public function delete_rows($query, $rows)
    {
        $updates = ['deleted_by' => Auth::user()->id, 'deleted_at' => Carbon::now()->toDateTimeString()];

        return $query->whereIn('id', $rows)->update($updates);
    }

    public function export_my_rows($query)
    {
        $head = $this->field->columns->map(function ($column) { return 'C' . $column->id; })->toArray();

        $encrypts = $this->field->columns->filter(function ($column) { return $column->encrypt; });

        $rows = array_map(function ($row) use ($encrypts) {
            $this->setEncrypts($row, $encrypts);
            return array_values(get_object_vars($row));
        }, $query->whereNull('deleted_at')->select($head)->get());

        array_unshift($rows, $this->field->columns->fetch('name')->toArray());

        return $rows;
    }

    public function exportAllRows($query)
    {
        $head = $this->field->columns->map(function ($column) { return 'C' . $column->id; })->toArray();

        $rows = array_map(function ($row) {

            return array_values(get_object_vars($row));

        }, $query->whereNull('deleted_at')->select($head)->get());

        array_unshift($rows, $this->field->columns->fetch('title')->toArray());

        return $rows;
    }

    public function setEncrypts($row, $encrypts)
    {
        $encrypts->each(function ($encrypt) use ($row) {
            $column = 'C' . $encrypt->id;

            $encrypted = mb_substr($row->$column, round(mb_strlen($row->$column)/2));

            $row->$column = str_pad($encrypted, strlen($row->$column), "*", STR_PAD_LEFT);
        });
    }

    public function setAnswers($column)
    {
        switch ($column->rules) {
            case 'counties':
                $items = DB::table('plat_public.dbo.lists')->lists('name', 'code');
                break;

            case 'gender':
                $items =  ['1' => '男', '2' => '女'];
                break;

            case 'bool':
                $items = ['0' => '否', '1' => '是'];
                break;

            case 'gateway':
                $items = ['0' => '無', '1' => '師培系所之師資生', '2' => '師培中心之師資生'];
                break;

            case 'menu':
                $column->answers->lists('title', 'value');
                $items = [];
                break;

            default:
                break;
        }

        foreach ($items as $value => $title) {
            $column->answers->push(['value' => $value, 'title' => $title]);
        }
    }

    public function queryValueInColumn($column_name)
    {
        return DB::table($this->fullDataTable)
            ->whereNull('deleted_at')
            ->where($column_name, 'like', '%' . Input::get('query') . '%')
            ->groupBy($column_name)
            ->select($column_name . ' AS text')
            ->limit(100)
            ->get();
    }

    public function checker($name)
    {
        $checkers = [
            'stdidnumber' => function ($column_value, $column, &$column_errors) {
                !check_id_number($column_value) && array_push($column_errors, $column->title . '無效');
            },
            'schid_104' => function ($column_value, $column, &$column_errors) {
                !isset($this->temp->works) && $this->temp->works = $this->get_own_organizations(1);
                !in_array($column_value, $this->temp->works, true) && array_push($column_errors, '不是本校代碼');
            },
            'schid_105' => function ($column_value, $column, &$column_errors) {
                !isset($this->temp->works) && $this->temp->works = $this->get_own_organizations(1);
                !in_array($column_value, $this->temp->works, true) && array_push($column_errors, '不是本校代碼');
            },
            'depcode_104' => function ($column_value, $column, &$column_errors) {
                !isset($this->temp->dep_codes_104) && $this->temp->dep_codes_104 = DB::table('rows.dbo.row_20150910_175955_h23of')
                    ->whereIn('C246', $this->get_own_organizations(1))->lists('C248');
                !in_array($column_value, $this->temp->dep_codes_104, true) && array_push($column_errors, '不是本校科別代碼');
            },
            'depcode_105' => function ($column_value, $column, &$column_errors) {
                !isset($this->temp->dep_codes_105) && $this->temp->dep_codes_105 = DB::table('rows.dbo.row_20160622_111650_ykezh')
                    ->whereIn('C1106', $this->get_own_organizations(1))->lists('C1108');
                !in_array($column_value, $this->temp->dep_codes_105, true) && array_push($column_errors, '不是本校科別代碼');
            },
            'tted_sch' => function ($column_value, $column, &$column_errors) {
                !isset($this->temp->schools) && $this->temp->schools = $this->get_own_organizations(2);
                !in_array($column_value, $this->temp->schools, true) && array_push($column_errors, '不是本校代碼');
            },
            'tted_depcode_103' => function ($column_value, $column, &$column_errors) {
                !isset($this->temp->dep_codes_103) && $this->temp->dep_codes_103 = DB::table('plat_public.dbo.pub_depcode_tted')
                    ->whereIn('sch_id', $this->get_own_organizations(2))->where('year','=','103')->lists('id');
                !in_array($column_value, $this->temp->dep_codes_103, true) && array_push($column_errors, '不是本校系所代碼');
            },
            'tted_depcode_104' => function ($column_value, $column, &$column_errors) {
                !isset($this->temp->dep_codes_104) && $this->temp->dep_codes_104 = DB::table('plat_public.dbo.pub_depcode_tted')
                    ->whereIn('sch_id', $this->get_own_organizations(2))->where('year','=','104')->lists('id');
                !in_array($column_value, $this->temp->dep_codes_104, true) && array_push($column_errors, '不是本校系所代碼');
            },
            'tted_depcode_105' => function ($column_value, $column, &$column_errors) {
                !isset($this->temp->dep_codes_105) && $this->temp->dep_codes_105 = DB::table('plat_public.dbo.pub_depcode_tted')
                    ->whereIn('sch_id', $this->get_own_organizations(2))->where('year','=','104')->orWhere('year','=','105')->lists('id');
                !in_array($column_value, $this->temp->dep_codes_105, true) && array_push($column_errors, '不是本校系所代碼');
            },
            'junior_schools_in_city' => function ($column_value, $column, &$column_errors) {
                !isset($this->temp->junior_schools_in_city) && $this->temp->junior_schools_in_city = DB::table('rows.dbo.row_20151022_135158_5xtfu')
                    ->whereIn('C404', $this->get_own_organizations(1))->lists('C406');
                !in_array($column_value, $this->temp->junior_schools_in_city, true) && array_push($column_errors, '不是本縣市所屬學校代碼');
            },
            'counties' => function ($column_value, $column, &$column_errors) {
                !isset($this->temp->counties) && $this->temp->counties = DB::table('plat_public.dbo.lists')->lists('code');
                !in_array($column_value, $this->temp->counties, true) && array_push($column_errors, '不是正確的縣市代碼');
            },
        ];
        return $checkers[$name];
    }

    public function count($query)
    {
        return $this->field->builded_at ? $query->count() : 0;
    }

    public function columns()
    {
        return $this->field->columns->fetch('name')->toArray();
    }

    public function replicate($field)
    {
        $this->field->name = self::generate_table();

        $this->field->lock = true;

        $this->field->save();

        $this->field->depends()->attach($field->id);

        $field->columns->each(function ($column) {

            $this->field->columns()->save($column->replicate());

        });
    }

    public function getFullDataTable()
    {
        return $this->field->database . '.dbo.' . $this->field->name;
    }

    public function getParentTable()
    {
        return $this->exists() ? [$this->field] : [];
    }

    public function remove_column($column_id)
    {
        $column = $this->field->columns->find($column_id);

        if (Schema::hasColumn($this->getFullDataTable(), $column->name)) {
            Schema::table($this->getFullDataTable(), function ($table) use ($column) {
                $table->dropColumn($column->name);
            });
        }

        return $column->delete();
    }

    public function update_column($column_id, $attributes)
    {
        $column = $this->field->columns()->save(Field::findOrNew($column_id)->fill($attributes));

        $name = isset($attributes['name']) ? $attributes['name'] : 'C' . $column->id;

        $column->update(['name' => $name]);

        Schema::table($this->getFullDataTable(), function ($table) use ($column) {
            $table->string($column->name)->nullable();
        });

        return $column;
    }

    public function insert($values = [])
    {
        DB::table($this->getFullDataTable())->insert(array_merge([
            'updated_at' => Carbon::now()->toDateTimeString(),
            'created_at' => Carbon::now()->toDateTimeString(),
        ], $values));
    }

    public function deleteRow($attributes)
    {
        DB::table($this->getFullDataTable())->where($attributes)->delete();
    }

    public function getFieldData($attributes, $key)
    {
        return DB::table($this->getFullDataTable())->where($attributes)->select($key.' as value')->first();
    }

    public function rowExists($attributes)
    {
        return DB::table($this->getFullDataTable())->where($attributes)->exists();
    }

    public function getRow($attributes)
    {
        return DB::table($this->getFullDataTable())->where($attributes)->first();
    }

    public function put($attributes, array $values)
    {
        DB::table($this->getFullDataTable())->where($attributes)->update($values);
    }

    public function setAttributesFieldName($attributes)
    {
        return Field::find(array_keys($attributes))->map(function ($field) use ($attributes) {
            return ['name' => $field->name, 'value' => $attributes[$field->id]];
        })->lists('value', 'name');
    }
}