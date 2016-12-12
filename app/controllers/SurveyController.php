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
        $node = Input::has('node.id')
            ? SurveyORM\Node::find(Input::get('node.id'))->next
            : SurveyORM\book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->first();

        return ['node' => $node];
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
        $class = Input::get('parent.class');

        $nodes = Input::has('answer') ? $class::find(Input::get('parent.id'))->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions', 'answers']) : [];

        return ['nodes' => $nodes];
    }

}
