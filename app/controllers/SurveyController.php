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
        $answers = DB::table('35')->first();

        if (!$answers) {
            $page = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->first();
            DB::table('35')->insert(['page_id' => $page->id, 'created_by' => 1]);
        } else {
            $previous = SurveyORM\Node::find($answers->page_id);
            $page = Input::get('next') ? $previous->next : $previous;
            DB::table('35')->update(['page_id' => $page->id]);
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
        $nodes = SurveyORM\Node::find(Input::get('page.id'))->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions', 'answers']);

        return ['nodes' => $nodes];
    }


    /**
     * Show children nodes.
     *
     * @return Response
     */
    public function getChildren()
    {
        if (Input::has('parent')) {
            $class = Input::get('parent.class');

            $nodes = $class::find(Input::get('parent.id'))->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions', 'answers']);
        } else {
            $nodes = [];
        }

        Input::has('value') && DB::table('35')->update([Input::get('question.id') => Input::get('value')]);sleep(1);

        return ['nodes' => $nodes];
    }

}
