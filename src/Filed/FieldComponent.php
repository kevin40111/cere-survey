<?php

namespace Cere\Survey;

use User;
use Files;
use DB;
use View;
use Response;
use Input;
use RequestFile;
use Row\Column;
use Illuminate\Support\MessageBag;
use Cere\Survey\Field\SheetRepository;
use Cere\Survey\Field\FieldRepository;

/**
 * Rows data Repository.
 *
 */
class FieldComponent extends CommFile
{
    protected $database = 'rows';

    protected $temp;

    /**
     * Create a new RowsFile.
     *
     * @param  Files  $file
     * @param  User  $user
     * @return void
     */
    function __construct(Files $file, User $user)
    {
        parent::__construct($file, $user);

        $this->temp = (object)[];

        $this->configs = $this->file->configs->lists('value', 'name');
    }

    /**
     * Determine if the view full page.
     *
     * @return bool
     */
    public function is_full()
    {
        return false;
    }

    /**
     * Get all views.
     *
     * @return array
     */
    public function get_views()
    {
        return ['open', 'import', 'rows', 'analysis', 'setRowsOwnerView'];
    }

    public static function tools()
    {
        return [
            //['name' => 'edit_information', 'title' => '編輯檔案資訊', 'method' => 'edit_information', 'icon' => 'edit'],
            ['name' => 'analysis',         'title' => '分析結果',     'method' => 'analysis',         'icon' => 'pie-chart'],
            ['name' => 'rows',             'title' => '編輯資料列',   'method' => 'rows',             'icon' => 'create'],
            ['name' => 'import',           'title' => '匯入資料',     'method' => 'import',           'icon' => 'file-upload'],
            ['name' => 'export',           'title' => '匯出資料',     'method' => 'exportAllRows',    'icon' => 'file-download'],
            ['name' => 'setRowsOwnerView', 'title' => '設定資料列擁有人', 'method' => 'setRowsOwnerView',     'icon' => 'assignment-ind'],
        ];
    }

    /**
     * Add a file then initialize sheets and tables.
     *
     * @return void
     */
    public function create()
    {
        parent::create();

        $sheet = $this->file->sheets()->save(SheetRepository::create()->sheet);

        SheetRepository::target($sheet)->init();
    }

    /**
     * Get default view when open file path.
     *
     * @return string
     */
    public function open()
    {
        $view = $this->isCreater() ? 'files.rows.table_editor' : 'files.rows.table_open';

        return $view;
    }

    /**
     * Get sub tool view path.
     *
     * @return string
     */
    public function subs()
    {
        return View::make('files.rows.subs.' . Input::get('tool', ''))->render();
    }

    /**
     * Get import rows data view path.
     *
     * @return string
     */
    public function import()
    {
        return !empty($this->configs['rows_edit']) && $this->configs['rows_edit'] == 1 ? self::rows() : 'files.rows.table_import';
    }

    /**
     * Get edit rows data view path.
     *
     * @return string
     */
    public function rows()
    {
        return 'files.rows.rows_editor';
    }

    /**
     * Get analysis rows data view path.
     *
     * @return string
     */
    public function analysis()
    {
        return 'files.analysis.analysis';
    }

    /**
     * Get set rows owner view path.
     *
     * @return string
     */
    public function setRowsOwnerView()
    {
        return 'files.rows.set_rows_owner';
    }

    private function init()
    {
        $isCreater = $this->isCreater() || Input::get('editor');

        if ($this->file->sheets->isEmpty()) {
            $this->file->sheets()->save(SheetRepository::create()->sheet);
        }

        $sheets = $this->file->load('sheets')->sheets->each(function ($sheet) use ($isCreater) {
            SheetRepository::target($sheet)->init()->count($this->isCreater());
        });

        return $sheets;
    }

    public function get_file()
    {
        $sheets = $this->init();

        $sheets->first()->selected = true;

        return [
            'title'    => $this->file->title,
            'sheets'   => $sheets->toArray(),
            'rules'    => FieldRepository::$rules,
            'comment'  => $this->get_information()->comment,
        ];
    }

    public function update_sheet()
    {
        $sheet = $this->file->sheets()->with(['tables', 'tables.columns'])->find(Input::get('sheet')['id']);

        $sheet->update(['title' => Input::get('sheet')['title'], 'editable' => Input::get('sheet')['editable']]);

        return ['sheet' => $sheet->toArray()];
    }

    public function remove_column()
    {
        $deleted = FieldRepository::target($this->file->sheets->find(Input::get('sheet_id'))->tables->find(Input::get('table_id')))->remove_column(Input::get('column.id'));

        return ['deleted' => $deleted];
    }

    public function update_column()
    {
        $values = Input::only(['column.name', 'column.title', 'column.rules', 'column.unique', 'column.encrypt', 'column.isnull', 'column.readonly'])['column'];

        $column = FieldRepository::target($this->file->sheets->find(Input::get('sheet_id'))->tables->find(Input::get('table_id')))->update_column(Input::get('column.id'), $values);

        return ['column' => $column];
    }

    public function update_comment()
    {
        $information = $this->get_information();

        $information->comment = urldecode(base64_decode(Input::get('comment', '')));

        $this->put_information($information);

        return ['comment' => $information->comment];
    }

    private function put_information($information)
    {
        $this->file->information = json_encode($information);

        $this->file->save();
    }

    private function get_information()
    {
        return isset($this->file->information) ? json_decode($this->file->information) : (object)['comment' => ''];
    }

    public function import_upload()
    {
        if (!Input::hasFile('file_upload'))
            throw new UploadFailedException(new MessageBag(['messages' => ['max' => '檔案格式或大小錯誤']]));

        $file = new Files(['type' => 3, 'title' => Input::file('file_upload')->getClientOriginalName()]);

        $file_upload = new CommFile($file, $this->user);

        $file_upload->upload(Input::file('file_upload'));

        $table = $this->file->sheets->first()->tables->first();

        $rows = \Excel::selectSheetsByIndex(0)->load(storage_path() . '/file_upload/' . $file_upload->file->file, function ($reader) {

        })->get(FieldRepository::target($table)->columns())->toArray();

        list($messages, $amounts) = FieldRepository::target($table)->import($rows, $this->user->id);

        $messages_error = array_values(array_filter($messages, function ($message) {
            return !$message->pass;
        }));

        return ['messages' => $messages_error, 'amounts' => $amounts];
    }

    /**
     * Sent a request to import file.
     */
    public function request_to()
    {
        $input = Input::only('groups', 'description');

        $myGroups = $this->user->groups;

        if ($this->isCreater()) {
            foreach($input['groups'] as $group) {
                if (count($group['users']) == 0 && $myGroups->contains($group['id'])){
                    RequestFile::updateOrCreate(
                        ['target' => 'group', 'target_id' => $group['id'], 'doc_id' => $this->doc->id, 'created_by' => $this->user->id],
                        ['description' => $input['description']]
                    );
                }
                if (count($group['users']) != 0){
                    foreach($group['users'] as $user){
                        RequestFile::updateOrCreate(
                            ['target' => 'user', 'target_id' => $user['id'], 'doc_id' => $this->doc->id, 'created_by' => $this->user->id],
                            ['description' => $input['description']]
                        );
                    }
                }
            }
        }

        return Response::json(Input::all());
    }

    public function export_sample()
    {
        SheetRepository::target($this->file->sheets->first())->export_sample();
    }

    public function export_my_rows()
    {
        SheetRepository::target($this->file->sheets->find(Input::get('sheet_id')))->export_my_rows($this->user->id);
    }

    public function exportAllRows()
    {
        if (!$this->isCreater())
            throw new FileFailedException(new MessageBag(array('noAuth' => '沒有權限')));

        SheetRepository::target($this->file->sheets->first())->exportAllRows();
    }

    //uncomplete only first sheet, only first table
    public function get_rows()
    {
        $lock = !empty($this->configs['rows_edit']) && $this->configs['rows_edit'] == 1 ? true : false;

        $search = Input::has('search.text') && Input::has('search.column_id') ? Input::get('search') : null;

        $paginate = SheetRepository::target($this->file->sheets->first())->get_rows($search, $this->isCreater());

        return ['paginate' => $paginate->toArray(), 'lock' => $lock];
    }

    //uncomplete only first sheet
    public function delete_rows()
    {
        $tables = SheetRepository::target($this->file->sheets->first())->delete_rows(Input::get('rows'), $this->isCreater());
    }

    public function get_own_organizations($project_id)
    {
        return \Plat\Member::where('project_id', $project_id)->where('user_id', $this->user->id)->first()->organizations->load('every')->map(function ($organization) {
            return $organization->every->lists('id');
        })->flatten()->toArray();
    }

    /**
     * @todo delete all relation model
     */
    public function delete()
    {
        $this->doc->shareds->each(function ($requested) {
            $requested->delete();
        });

        $this->doc->requesteds->each(function ($requested) {
            $requested->delete();
        });

        return ['deleted' => parent::delete()];
    }

    /**
     * Get analysis questions
     */
    public function get_analysis_questions()
    {
        $questions = [];
        $sheets = $this->file->sheets()->with(['tables', 'tables.columns'])->get()->each(function ($sheet) use (&$questions) {
            $sheet->tables->each(function ($table) use (&$questions) {
                $table->columns->each(function ($column) use (&$questions, $table) {
                    $answers = array_map(function ($answer) {
                        return ['title' => $answer->value, 'value' => $answer->value];
                    }, DB::table($table->database . '.dbo.' . $table->name)->groupBy('C' . $column->id)->select('C' . $column->id . ' AS value')->get());
                    array_push($questions, ['name' => $column->id, 'title' => $column->title, 'choosed' => true, 'answers' => $answers]);
                });
            });
        });
        return ['questions' => $questions, 'title' => ''];
    }

    /**
     * Get analysis filter columns
     */
    public function get_targets()
    {
        return [
            'targets' => [
                'groups' => [
                    'all' => ['key' => 'all', 'name' => '不篩選', 'targets' => ['all' => ['name' => '全部', 'selected' => true]]]
                ]
            ]
        ];
    }

    /**
     * Analysis frequence
     * @todo check table and columns exist
     */
    public function get_frequence()
    {
        $id = Input::get('name');

        $table = Column::find($id)->inTable;

        $data_query = DB::table($table->database . '.dbo.' . $table->name);

        $frequence = $data_query->groupBy('C' . $id)->select(DB::raw('count(*) AS total'), DB::raw('CAST(C' . $id . ' AS varchar) AS name'))->remember(3)->lists('total', 'name');

        return ['frequence' => $frequence];
    }

    /**
     * Get analysis filter columns
     */
    private function getUniqueExists($uniques, $table, $column)
    {

    }

    public function updateRows()
    {
        $updated = array_map(function ($row) {

            return SheetRepository::target($this->file->sheets->first())->updateRow($row, $this->isCreater());

        }, Input::get('rows'));

        return ['updated' => $updated];
    }

    public function saveAs()
    {
        $doc = parent::saveAs();

        $this->file->sheets->each(function ($sheet) use ($doc) {

            $cloneSheet = $sheet->replicate();

            $cloneSheet = $doc->is_file->sheets()->save($cloneSheet);

            SheetRepository::target($cloneSheet)->replicate($sheet);

        });
    }

    public function getParentTable()
    {
        return SheetRepository::target($this->file->sheets->first())->getParentTable();
    }

    public function cloneTableData()
    {
        SheetRepository::target($this->file->sheets->first())->cloneTableData(Input::get('table_id'), $this->user->id);
    }

    public function queryValueInColumn()
    {
        $column_name = 'C' . Input::get('column_id');

        $values = FieldRepository::target($this->file->sheets->first()->tables->first())->queryValueInColumn($column_name);

        return ['values' => $values];
    }

    public function queryUsersByEmail()
    {
        $users = User::where('email', 'like', '%' . Input::get('query') . '%')->limit(1000)->get(['id', 'email', 'username']);

        return ['users' => $users];
    }

    public function getProjectPositions()
    {
        return ['positions' => $this->user->members->sortByDesc('logined_at')->first()->project->positions];
    }

    public function setRowsOwner()
    {
        if (!$this->isCreater()) {
            $message = '您沒有權限更改資料';
        } else {
            $table = $this->file->sheets->first()->tables->first();

            $column_name = 'C' . Input::get('selected.column_id');

            $updated = DB::table($table->database . '.dbo.' . $table->name)
                ->where($column_name, Input::get('selected.value_text'))->update(['created_by' => Input::get('selected.user.id')]);

            $message = $updated . '筆資料已儲存';
        }

        return ['message' => $message];
    }

    /**
     * no UI
     */
    public function generate_uniques()
    {
        $table = $this->file->sheets->each(function ($sheet) {
            $sheet->tables->each(function ($table) {
                $columns = $table->columns->filter(function ($column) {
                    return $column->unique && $column->rules=='stdidnumber';
                });

                list($query, $power) = $this->get_rows_query([$table]);

                $rows = $query->whereNotExists(function ($query) use ($table, $columns) {
                    $query->from($table->database . '.dbo.' . $table->name . '_map AS map');
                    foreach($columns as $column) {
                        $query->whereRaw('C' . $column->id . ' = map.stdidnumber');
                    }
                    $query->select(DB::raw(1));
                })
                ->whereNull('deleted_at')
                ->select($columns->map(function ($column) { return 'C' . $column->id . ' AS stdidnumber'; })->toArray())
                ->get();

                foreach(array_chunk($rows, 50) as $part) {
                    $newcids = array_map(function ($row) {
                        return [
                            'stdidnumber' => $row->stdidnumber,
                            'newcid' => createnewcid(strtoupper($row->stdidnumber))
                        ];
                    }, $part);

                    DB::table($table->database . '.dbo.' . $table->name . '_map')->insert($newcids);
                }
            });
        });
    }
}
