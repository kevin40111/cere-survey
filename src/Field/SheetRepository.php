<?php

namespace Cere\Survey\Field;

use Carbon\Carbon;
use Schema;
use DB;
use Auth;
use Cere\Survey\Eloquent\Field\Sheet;

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
        $fieldRepository = FieldRepository::create(Auth::user()->id);

        $this->sheet->tables()->save($fieldRepository->field);

        $fieldRepository->init();

        $this->sheet->load('tables');

        return $fieldRepository;
    }

    public function field()
    {
        return FieldRepository::target($this->sheet->tables()->first(), Auth::user()->id);
    }

    public function replicateTo($file)
    {
        $sheet = self::create();

        $file->sheets()->save($sheet->getModel());

        $this->sheet->tables->each(function ($table) use ($sheet) {

            FieldRepository::target($table, Auth::user()->id)->replicateTo($sheet->getModel());

        });

        return $sheet;
    }

    public function get_rows($search, $isCreater)
    {
        list($query, $power) = $this->get_rows_query($isCreater);

        return FieldRepository::target($this->sheet->tables->first(), Auth::user()->id)->get_rows($query, $search);
    }

    public function updateRow($row, $isCreater)
    {
        list($query, $power) = $this->get_rows_query($isCreater);

        return FieldRepository::target($this->sheet->tables->first(), Auth::user()->id)->updateRow($query, $row);
    }

    public function delete_rows($rows, $isCreater)
    {
        list($query, $power) = $this->get_rows_query($isCreater);

        FieldRepository::target($this->sheet->tables->first(), Auth::user()->id)->delete_rows($query, $rows);
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

                $rows = FieldRepository::target($this->sheet->tables->first(), Auth::user()->id)->export_my_rows($query);

                $sheet->freezeFirstRow();

                $sheet->fromArray($rows, null, 'A1', false, false);
            });

        })->download('xls');
    }

    public function exportAllRows()
    {
        return \Excel::create('sample', function ($excel) {

            $excel->sheet('sample', function ($sheet) {

                list($query, $power) = $this->get_rows_query(true);

                $rows = FieldRepository::target($this->sheet->tables->first(), Auth::user()->id)->exportAllRows($query);

                $sheet->freezeFirstRow();

                $sheet->fromArray($rows, null, 'A1', false, false);

            });

        });
    }

    //uncomplete
    private function get_rows_query($isOwner = false)
    {
        foreach($this->sheet->tables as $index => $table) {
            if ($index==0) {
                $query = DB::table(FieldRepository::target($table, Auth::user()->id)->getFullDataTable());
            } else {
                //join not complete
                //$rows_query->leftJoin(FieldRepository::target($table, Auth::user()->id)->getFullDataTable() . ' AS t' . $index, 't' . $index . '.' . $table->primaryKey, '=', 't0.'.$table->primaryKey);
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

    public function count($isCreater)
    {
        list($query, $power) = $this->get_rows_query($isCreater);

        $this->sheet->load('tables.columns')->tables->each(function ($table) use ($query) {

            $table->count = FieldRepository::target($table, Auth::user()->id)->count($query);

        });
    }

    public function getModel()
    {
        return $this->sheet;
    }
}