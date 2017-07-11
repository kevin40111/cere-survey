<?php

namespace Plat\Field;

use Carbon\Carbon;
use Schema;
use DB;
use Auth;
use Plat\Eloquent\Field\Sheet;

class SheetRepository
{
    function __construct($sheet)
    {
        $this->sheet = $sheet;
    }

    public static function target($target)
    {
        return new self($target);
    }

    public static function create()
    {
        $sheet = new Sheet(['title' => '', 'editable' => true, 'fillable' => true]);

        return new self($sheet);
    }

    public function init()
    {
        if ($this->sheet->tables->isEmpty()) {
            $this->addField();
        }

        return $this;
    }

    public function addField()
    {
        $fieldRepository = FieldRepository::create();

        $this->sheet->tables()->save($fieldRepository->field);

        $fieldRepository->init();

        $this->sheet->load('tables');

        return $fieldRepository;
    }

    public function field()
    {
        return FieldRepository::target($this->sheet->tables()->first());
    }

    public function replicate($sheet)
    {
        $cloneTables = $sheet->tables->each(function ($table) {

            $cloneTable = $table->replicate();

            $cloneTable = $this->sheet->tables()->save($cloneTable);

            $cloneTable = FieldRepository::target($cloneTable)->replicate($table);

        });
    }

    public function get_rows($search, $isCreater)
    {
        list($query, $power) = $this->get_rows_query($isCreater);

        return FieldRepository::target($this->sheet->tables->first())->get_rows($query, $search);
    }

    public function updateRow($row, $isCreater)
    {
        list($query, $power) = $this->get_rows_query($isCreater);

        return FieldRepository::target($this->sheet->tables->first())->updateRow($query, $row);
    }

    public function delete_rows($rows, $isCreater)
    {
        list($query, $power) = $this->get_rows_query($isCreater);

        FieldRepository::target($this->sheet->tables->first())->delete_rows($query, $rows);
    }

    public function export_sample()
    {
        \Excel::create('sample', function ($excel) {

            $excel->sheet('sample', function ($sheet) {

                $table = $this->sheet->tables->first();

                $sheet->freezeFirstRow();

                $sheet->fromArray($table->columns->fetch('name')->toArray());

            });

        })->download('xls');
    }

    public function export_my_rows()
    {
        \Excel::create('sample', function ($excel) {

            $excel->sheet('sample', function ($sheet) {

                list($query, $power) = $this->get_rows_query();

                $rows = FieldRepository::target($this->sheet->tables->first())->export_my_rows($query);

                $sheet->freezeFirstRow();

                $sheet->fromArray($rows, null, 'A1', false, false);
            });

        })->download('xls');
    }

    public function exportAllRows()
    {
        \Excel::create('sample', function ($excel) {

            $excel->sheet('sample', function ($sheet) {

                list($query, $power) = $this->get_rows_query(true);

                $rows = FieldRepository::target($this->sheet->tables->first())->exportAllRows($query);

                $sheet->freezeFirstRow();

                $sheet->fromArray($rows, null, 'A1', false, false);

            });

        })->download('xlsx');
    }

    //uncomplete
    private function get_rows_query($isOwner = false)
    {
        foreach($this->sheet->tables as $index => $table) {
            if ($index==0) {
                $query = DB::table(FieldRepository::target($table)->getFullDataTable());
            } else {
                //join not complete
                //$rows_query->leftJoin(FieldRepository::target($table)->getFullDataTable() . ' AS t' . $index, 't' . $index . '.' . $table->primaryKey, '=', 't0.'.$table->primaryKey);
            }
        }
        $power = [];

        if (!$isOwner) {
            $query->where('created_by', Auth::user()->id);
        } else {
            $query->addSelect('created_by');
        }

        return [$query, $power];
    }

    public function cloneTableData($parent_table_id, $owner_id)
    {
        $child['table']     = $this->sheet->tables->first();
        $child['columns']   = $child['table']->columns->lists('id','name');
        $child['has_table'] = FieldRepository::target($child['table'])->has_table();
        $child['rows']      = [];

        $parent['table']    = Table::find($parent_table_id);
        $parent['sheet']    = $parent['table']->sheet;
        $parent['columns']  = $parent['table']->columns->lists('name','id');
        $parent['rows']     = DB::table(FieldRepository::target($parent['table'])->getFullDataTable())->where('created_by',$this->user->id)->whereNull('deleted_at')->get();

        if (!$child['has_table']) {
            FieldRepository::target($child['table'])->table_build();
            $child['has_table'] = FieldRepository::target($child['table'])->has_table();
        }

        if ($parent['rows']) {
            $count = 0;
            foreach ($parent['rows'] as $row) {
                foreach ($parent['table']['columns'] as $column) {
                    $columnTitle    = $parent['columns'][$column->id];
                    $childColumnId  = $child['columns'][$columnTitle];
                    $child['rows'][$count]['C'.$childColumnId] = $row->{'C'.$column->id};
                    $child['rows'][$count]['file_id'] = $parent['sheet']->file_id;
                    $child['rows'][$count]['created_by'] = $owner_id;
                    $child['rows'][$count]['updated_by'] = $owner_id;
                    $child['rows'][$count]['created_at'] = Carbon::now()->toDateTimeString();
                    $child['rows'][$count]['updated_at'] = Carbon::now()->toDateTimeString();
                }
                $count++;
            }
            foreach (array_chunk($child['rows'], 50) as $child_row) {
                $rowInsert = DB::table(FieldRepository::target($child['table'])->getFullDataTable())->insert($child_row);
            }
        }
        // return ['child'=>$child,'parent'=>$parent];
    }

    public function getParentTable()
    {
        $table = $this->sheet->tables->first();

        return $table->depends->isEmpty() ? [] : FieldRepository::target($table->depends->first())->getParentTable();
    }

    public function count($isCreater)
    {
        list($query, $power) = $this->get_rows_query($isCreater);

        $this->sheet->load('tables.columns')->tables->each(function ($table) use ($query) {

            $table->count = FieldRepository::target($table)->count($query);

        });
    }
}