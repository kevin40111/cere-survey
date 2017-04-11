<?php

use Plat\Eloquent\Survey as SurveyORM;
use Plat\Survey\SurveySession;
use Plat\Survey\SurveyRepositoryInterface;
use Plat\Survey;

class SurveyController extends \BaseController {
    /**
     * init the repository of the survey.
     *
     * @param  int  $book_id
     * @param  string  $type
     * @return Response
     */
    function __construct(SurveyRepositoryInterface $repository)
    {
        $this->user_id = $repository->getId();
        $this->type = $repository->getType();
        $this->repository = $repository;
    }

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
     * Display a page of the survey login.
     *
     * @return Response
     */
    public function surveyLogin()
    {
        SurveySession::logout();

        return View::make('layout-survey')->nest('context', 'files.survey.surveylogin-ng');
    }

    /**
     * Display a page of the demo login.
     *
     * @return Response
     */
    public function demoLogin()
    {
        return View::make('layout-survey')->nest('context', 'files.survey.demologin-ng');
    }

    /**
     * Check if login user in population.
     *
     * @param  int  $book_id
     * @return Response
     */
    public function checkInRows($book_id)
    {
        SurveySession::logout();

        $login_id = Input::get('id');

        $file_book = SurveyORM\Book::find($book_id);

        $table = Files::find($file_book->rowsFile_id)->sheets->first()->tables->first();

        $in_rows  = DB::table('rows.dbo.'.$table->name)->where('C'.$file_book->loginRow_id, $login_id)->exists();

        if (!$in_rows) {
             return Redirect::to('survey/'.$book_id.'/survey/surveyLogin');
        }

        $encrypt_id = SurveySession::login($book_id, $login_id);
        if (!$this->repository->exist($encrypt_id)) {
            $this->repository->increment($encrypt_id, ['page_id' => null]);
        }

        return Redirect::to('survey/'.$book_id.'/survey/page');
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
    public function getNextNode($book_id, $has_missing = true)
    {
        $answers = (object)$this->repository->all($this->user_id);
        $url = null;
        $previous = is_null($answers->page_id) ? null : SurveyORM\Node::find($answers->page_id) ;//已填答頁數
        $page = is_null($previous) ? SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->first() : $previous->next;

        if (Input::get('next')) {
            //撿查是否有漏答

            if ($this->checkPage($page->id) == 'has_missing') {
                return ['node' => $page->load('rule'), 'answers' => $this->repository->all($this->user_id), 'url' => $url, 'hasMissing' => $has_missing];
            }  
            
            if ($page->next != '' && $this->checkPage($page->next->id) == 'has_jump') {
                $this->repository->put($this->user_id, 'page_id', $page->next->id);
                return $this->getNextNode($book_id, false);
            }

            $this->repository->put($this->user_id, 'page_id', $page->id); //update page
            
            $lastPage = is_null($page->next);
            $nextPage = $lastPage ? null : $page->next->load('rule');
        } else {
            $lastPage = is_null($page);
            $nextPage = $lastPage ? null : $page->load('rule');
        }

        if ($lastPage) {
            $extBooks = $this->getExtBook($book_id);
            $extended = (count($extBooks) == 0) ? false : true;
            if ($extended) {
                if ($this->type == 'survey') {
                    $book = SurveyORM\Book::find($book_id);
                    $rowsFile = Files::find($book->rowsFile_id)->sheets()->first()->tables()->first();
                    $userOrganization = DB::table('rows.dbo.'.$rowsFile->name)->where('C'.$book->loginRow_id, SurveySession::getLoginId())->select('C'.$book->column_id.' AS value')->first();

                    $extBook = $extBooks->filter(function ($extBook) use($userOrganization){
                        $values = array_fetch($extBook->rule->expressions[0]['conditions'], 'value');
                        return in_array($userOrganization->value, $values);
                    })->first();

                    $extBook_id = $extBook->id;

                    $encrypt_id = SurveySession::login($extBook_id, SurveySession::getLoginId());
                    if (!DB::table($extBook_id)->where('created_by', $encrypt_id)->exists()) {
                        DB::table($extBook_id)->insert(['page_id' => null, 'created_by' => $encrypt_id]);
                    }

                    $url = '/survey'.'/'.$extBook_id.'/survey/page';
                }
                if ($this->type == 'demo') {
                    $url = '/surveyDemo'.'/'.$book_id.'/demo/demoLogin';
                }
            }
        }

        return ['node' => $nextPage, 'answers' => $this->repository->all($this->user_id), 'url' => $url];
    }

    public function checkPage($page_id)
    {
        $answers = $this->repository->all($this->user_id);

        gettype($answers) == 'object' ? $answers = get_object_vars($answers) : '';

        $questions = SurveyORM\Node::find($page_id)->getQuestions();
        $page_jump=0;
        foreach ($questions as $question) {
            if ($answers[$question['id']] == null) {
                return 'has_missing';
            }else if ($answers[$question['id']] == -8) {
                $page_jump++;   
            }
        }

        if ($page_jump == sizeof($questions)) {
            return 'has_jump';
        }

        return 'has_next';
    }

    /**
     * Show nodes in a page node.
     *
     * @return Response
     */
    public function getNextNodes()
    {
        $nodes = SurveyORM\Node::find(Input::get('page.id'))->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions.rule', 'answers.rule', 'rule']);

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
            $nodes = $class::find(Input::get('parent.id'))->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions.rule', 'answers.rule', 'rule']);
        } else {
            $nodes = [];
        }

        Input::has('value') && $this->repository->put($this->user_id, Input::get('question.id'), Input::get('value'));

        if(Input::get('trigger') == 'saveAnswer'){

            if (Input::get('parent.class') == '') {
                $this->setChildQuestion(Input::get('parent.class'), Input::get('question.node_id'), Input::get('question.id'));
            } else {
                $this->setChildQuestion(Input::get('parent.class'), Input::get('parent.node_id'), Input::get('parent.id'));
            }

        return $this->setJumpQuestion();
        }

        return ['nodes' => $nodes, 'answers' => $this->repository->all(SurveySession::getHashId())];

    }

    public function setChildQuestion($input_class, $input_node_id, $input_select_id)
    {
        if ($input_class == SurveyORM\Answer::class) {

            $root_list = SurveyORM\Node::find($input_node_id)->answers;

            $selected = SurveyORM\Answer::find($input_select_id);

            foreach ($root_list as $root) {

                $this->initialParentListValue($root, 0, true);

            }

        $this->initialParentListValue($selected, 1);

        } else if($input_class == SurveyORM\Question::class || $input_class == '') {

            $root_list = SurveyORM\Node::find($input_node_id)->questions;

            $selected = SurveyORM\Question::find($input_select_id);

            Input::get('value') == '1' ? $initial_type = 1 : $initial_type = 0;

            foreach ($root_list as $root) {
                //judge (null) is for initial foreach same level of question value
                $this->repository->get(SurveySession::getHashId(), $root['id']) == null && $this->repository->put(SurveySession::getHashId(), $root['id'], '0');
                //judge (0) is for initial foreach same level question of child question
                $this->repository->get(SurveySession::getHashId(), $root['id']) == 0 && $this->initialParentListValue($root, 0);
            }

        $this->initialParentListValue($selected, $initial_type, true);

        }

    }


    public function setJumpQuestion()
    {
        $relates_rule = SurveyORM\SurveyRuleFactor::where('rule_relation_factor', Input::get('question.id'))->with('rules')->get();
        foreach ($relates_rule as $rule) {

            $node = SurveyORM\Node::find($rule->rules['effect_id']);
            
            if ($node->type == 'page') {
                $nodes = SurveyORM\Node::find($node->id)->sortByPrevious(['childrenNodes'])->childrenNodes->load(['questions']);
                $node_questions = array();
                foreach ($nodes as $node) {
                    foreach ($node->questions as $question) {
                        array_push($node_questions, $question);
                    }
                }
            } else {
                $node_questions = SurveyORM\Node::find($node->id)->questions;
            }
            //judge jumped node content's is answer or question
            sizeof(SurveyORM\Node::find($node->id)->answers) > 0 ? $root_list = SurveyORM\Node::find($node->id)->answers : $root_list = SurveyORM\Node::find($node->id)->questions;

            $answers =  array();

            $factors = SurveyORM\SurveyRuleFactor::where('rule_id', $rule->rule_id)->get();

            // get all of the factor value in repository for compareRule function
            foreach ($factors as $factor) {
                $question = $factor->rule_relation_factor;
                $answers[$question] = $this->repository->get(SurveySession::getHashId(), $factor->rule_relation_factor);
            }   
            
            if ($this->compareRule($rule->rules['id'], $answers)) {

                foreach ($root_list as $root) {

                    $this->initialParentListValue($root, 2);

                }

                foreach ($node_questions as $node_question) {

                    $this->repository->put(SurveySession::getHashId(), $node_question->id, -8);

                }


            } else {

                foreach ($root_list as $root) {

                    $this->initialParentListValue($root, 1);

                }

                foreach ($node_questions as $node_question) {

                    $this->repository->put(SurveySession::getHashId(), $node_question->id, null);

                }

            }

        }
    }

    /**
     * Initial select question and it's child question
     *
     * @param  int  $root=>get parent child, $initial_type (1) :  need answer, (2) :  don't need answer
     * @return Response
     */

    public function initialParentListValue($root, $initial_type, $force = false)
    {
        $initial_list = array();

        $initial_question = $root->getQuestions();

        foreach ($initial_question as $question) {

            if ($initial_type == 0) {

                $this->repository->get(SurveySession::getHashId(), $question['id']) == null || $force ? $initial_list[$question['id']] = -7 : '' ;

            } else if ($initial_type == 1) {

                $initial_list[$question['id']] = null;

            } else if ($initial_type == 2) {

                $initial_list[$question['id']] = -8;
            }

        }

        if (sizeof($initial_list) > 0) {
            foreach ($initial_list as $key => $value) {
                $this->repository->put(SurveySession::getHashId(), $key, $value);
            }
        }

        return $initial_list;
    }

    /**可能用不到，確定用不到再移除
     * Get rules.
     *
     * @return Response
     */
    /*public function getRule()
    {
        $class = Input::get('skipTarget.class');
        $root = $class::find(Input::get('skipTarget.id'));

        $rule = Survey\RuleRepository::target($root)->getRule();

        return ['rule' => $rule];
    }*/

    /**
     * Clean demo answers .
     *
     * @param  int  $book_id
     * @return Response
     */
    public function cleanAnswers($book_id)
    {
        $this->repository->decrement($this->user_id);

        return Redirect::to('surveyDemo/'.$book_id.'/demo/page');
    }

    /**
     * get extend books .
     *
     * @param  int  $book_id
     * @return Response
     */
    public function getExtBook($book_id)
    {
        return SurveyORM\Book::find($book_id)->applications->filter(function ($application) {
            return SurveyORM\Book::find($application->ext_book_id)->rule()->exists() && $application->extension ;
        })->map(function ($application) {
            return SurveyORM\Book::find($application->ext_book_id)->load('rule');
        });
    }

    /**
     * get demo options .
     *
     * @param  int  $book_id
     * @return Response
     */
    public function getDemoOption($book_id)
    {
        $options = [];
        foreach ($this->getExtBook($book_id)  as $extBook) {
            $values = array_fetch($extBook->rule->expressions[0]['conditions'], 'value');

            foreach ($values  as $value) {
                $option = [];
                $option['ext_book_id'] = $extBook->id;
                $option['organization_id'] = $value;
                $option['name'] = \Plat\Project\OrganizationDetail::where('id',$value)->orderBy('year', 'desc')->select('name')->first()->name;
                array_push($options,$option);
            }
        }

        return ['options' => $options];
    }

    /**
     * Compare rules.
     *
     * @return Response
     */
    public function compareRule($rule_id, $answer)
    {
        $status =Survey\RuleRepository::find($rule_id)->compareRule($rule_id, $answer);

        return $status;
    }
}
