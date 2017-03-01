<?php

use Plat\Eloquent\Survey as SurveyORM;


class SurveyController extends \BaseController {

    /**
     * Display a page of the survey.
     *
     * @return Response
     */
    public function page()
    {   
        return View::make('layout-survey')->nest('context', 'files.survey.demo-ng');
    }

    public function surveyLogin()
    {   
        Session::forget('id');

        return View::make('layout-survey')->nest('context', 'files.survey.surveylogin-ng');
    }

    public function getSurveyQuesion($book_id)
    {   
        $input_value     = Input::get('id');
        $file_book       = DB::table('file_book')->where('id', $book_id)->first();
        $row_sheets      = DB::table('row_sheets')->where('file_id', $file_book->rowsFile_id)->first();
        $row_tables      = DB::table('row_tables')->where('sheet_id', $row_sheets->id)->first();
        $has_value       = DB::table('rows.dbo.'.$row_tables->name)->where('C'.$file_book->loginRow_id, $input_value)->first();
        $answerer_repeat = DB::table($book_id)->where('created_by', $input_value)->first();

        if(!empty($answerer_repeat)){
            Session::put('id', $input_value);

            return Redirect::to('survey/'.$book_id.'/page');
        }else if(!empty($has_value)){

            Session::put('id', $input_value);
            $page = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->first();
            DB::table($book_id)->insert(['created_by' => $input_value, 'page_id' => $page->id]);

            return Redirect::to('survey/'.$book_id.'/page');
        }else{

            return Redirect::to('survey/'.$book_id.'/surveyLogin');
        }
    }

    /**
     * Show the book for survey.
     *
     * @param  int  $book_id
     * @return Response
     */
    public function getBook($book_id)
    {
        return ['book' => SurveyORM\Book::find($book_id)];
    }


    /**
     * Show a next node in book.
     *
     * @param  int  $book_id
     * @return Response
     */
    public function getNextNode($book_id)
    {   

        $answers = DB::table($book_id)->where('created_by',Session::get('id',Auth::user()->id))->first();

        if (!$answers) {
            $page = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->load(['rules'])->first();

            $answers = ['page_id' => $page->id, 'created_by' => Auth::user()->id] ;

            DB::table($book_id)->insert($answers);

        } else {
            $previous = SurveyORM\Node::find($answers->page_id)->load(['rules']);
            $page = Input::get('next') ? $previous->next : $previous;
            DB::table($book_id)->update(['page_id' => $page->id]);
        }

        return ['node' => $page, 'answers' => $answers];
    }


    /**
     * Show nodes in a page node.
     *
     * @return Response
     */
    public function getNextNodes()
    {
        $nodes = SurveyORM\Node::find(Input::get('page.id'))->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions.rules', 'answers.rules','rules']);

        return ['nodes' => $nodes];
    }


    /**
     * Show children nodes.
     *
     * @return Response
     */
    public function getChildren($book_id)
    {   
        if (Input::has('parent')) {
            $class = Input::get('parent.class');
            $nodes = $class::find(Input::get('parent.id'))->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions.rules', 'answers.rules','rules']);
        } else {
            $nodes = [];
        }

        Input::has('previous') && DB::table($book_id)->where('created_by', Session::get('id',Auth::user()->id))->update([Input::get('previous')=>null]);
        Input::has('value') && DB::table($book_id)->where('created_by', Session::get('id',Auth::user()->id))->update([Input::get('question.id') => Input::get('value')]);sleep(1);


        return ['nodes' => $nodes];
    }

    public function getRules()
    {
        $class = Input::get('skipTarget.class');
        $root = $class::find(Input::get('skipTarget.id'));

        if (!$root->rules()->first() == null) {
            $rules = $root->rules()->first()->expression;
        } else {
            $rules = null;
        }

        return ['rules' => $rules];
    }
}
