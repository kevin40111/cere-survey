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
        $answers = DB::table($book_id)->first();

        if (!$answers) {
            $page = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->load(['rules'])->first();

            $answers = ['page_id' => $page->id, 'created_by' => Auth::user()->id];
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

        Input::has('previous') && DB::table($book_id)->update([Input::get('previous')=>null]);
        Input::has('value') && DB::table($book_id)->update([Input::get('question.id') => Input::get('value')]);sleep(1);

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
