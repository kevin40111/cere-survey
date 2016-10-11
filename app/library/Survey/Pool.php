<?php

namespace Plat\Survey;

use Plat\Eloquent\Files\Survey\Set;
use Plat\Eloquent\Files\Survey\Origin;

class Pool {

    public function getPoolQuestions()
    {
        // $sbooks = Set\Book::find(Input::get('sBooks'))->load(['questions' => function($query) {
        //     $query->whereNull('parent_question_id');
        // }])->toArray();

        // $array = array_pluck($sbooks, 'questions');
        // var_dump($array);exit;
        $questions = Set\Question::with([
            'parent.is',
            'is',
        ])->where(function($query) {
            $query->whereNull('parent_question_id');
            // if (Input::has('type') && Input::get('type')!='?') {
            //     $query->where('type', Input::get('type'));
            // }
            $books = array_map(function($book) {
                return $book['id'];
            }, Input::get('sBooks'));
            $query->whereIn('book_id', $books);
        })->get();

        return ['questions' => $questions];
    }

    public function setPoolRootQuestion()
    {
        $sbook = $this->file->book->set;

        $sQuestions = $sbook->questions()->page(Input::get('pQuestion.page'))->whereNull('parent_question_id')->where(function($query) {
            if (!Input::has('pQuestion.parent_answer_id')) {
                $query->whereNull('parent_answer_id');
            } else {
                $query->where('parent_answer_id', Input::get('pQuestion.parent_answer_id'));
            }
        })->get();

        $this->sortQuestion(Input::get('pQuestion.sorter')*1, $sQuestions);

        $sQuestion = $sbook->questions()->save(
            new Set\Question([
                'page'        => Input::get('pQuestion.page'),
                'question_id' => Input::get('pQuestion.is.id'),
                'sorter'      => Input::get('pQuestion.sorter'),
                'parent_answer_id' => Input::get('pQuestion.parent_answer_id'),
            ])
        );

        $pQuestion = Set\Question::find(Input::get('pQuestion.id'));

        $sAnswers = $pQuestion->answers->map(function($sAnswer) {
            return new Set\Answer(['answer_id' => $sAnswer->answer_id]);
        });
        $sQuestion->answers()->saveMany($sAnswers->all());

        $sQuestion->load(['is','answers.is']);

        $csQuestions = $pQuestion->questions->map(function($cpQuestion) use ($sQuestion) {
            $csQuestion = $sQuestion->questions()->save(
                new Set\Question([
                    'book_id'          => $this->file->book->set->id,
                    'page'             => Input::get('pQuestion.page'),
                    'sorter'           => $cpQuestion->sorter,
                    'question_id'      => $cpQuestion->question_id,
                ])
            );
            $sAnswers = $cpQuestion->answers->map(function($sAnswer) {
                return new Set\Answer(['answer_id' => $sAnswer->answer_id]);
            });
            $csQuestion->answers()->saveMany($sAnswers->all());

            $csQuestion->load(['is','answers.is']);

            return $csQuestion;
        });

        return ['sQuestion' => $sQuestion, 'csQuestions' => $csQuestions];
    }

    public function setBranchQuestions($rQuestion, $pbQuestions)
    {
        return $pbQuestions->map(function($pbQuestion) use ($rQuestion) {
            $bQuestion = $rQuestion->questions()->save(
                new Set\Question([
                    'book_id'     => $this->file->book->set->id,
                    'question_id' => $pbQuestion->question_id,
                    'page'        => $rQuestion->page,
                    'sorter'      => $pbQuestion->sorter,
                ])
            );

            $pbAnswers = $pbQuestion->answers->map(function($pbAnswer) {
                return new Set\Answer(['answer_id' => $pbAnswer->answer_id]);
            });

            $bQuestion->answers()->saveMany($pbAnswers->all());

            $bQuestion->load(['is','answers.is']);

            return $bQuestion;
        });
    }

    public function setPoolBranchNormalQuestion()
    {
        $rQuestion = Set\Question::find(Input::get('bQuestion.parent_question_id'));
        $pQuestion = Set\Question::find(Input::get('pQuestion.id'));

        $this->sortQuestion(Input::get('bQuestion.sorter')*1, $rQuestion->questions);

        $bQuestion = $rQuestion->questions()->save(
            new Set\Question([
                'book_id'     => $this->file->book->set->id,
                'question_id' => $pQuestion->question_id,
                'page'        => $rQuestion->page,
                'sorter'      => Input::get('bQuestion.sorter'),
            ])
        );

        $pAnswers = $pQuestion->answers->map(function($pAnswer) {
            return new Set\Answer(['answer_id' => $pAnswer->answer_id]);
        });

        $bQuestion->answers()->saveMany($pAnswers->all());


        $bQuestion->load(['is','answers.is']);

        $bbQuestions = $this->setBranchQuestions($bQuestion, $pQuestion->questions);

        return ['question' => $bQuestion, 'bbQuestions' => $bbQuestions];
    }

    public function setPoolScaleBranchQuestion()
    {
        $sQuestion = Set\Question::find(Input::get('question.id'));
        $pQuestion = Set\Question::find(Input::get('pQuestion.id'));
        $sorter = $sQuestion->questions->max('sorter');

        $pQuestions = $pQuestion->questions->map(function($pbQuestion) use (&$sorter, $sQuestion) {
            $sorter++;
            $pQuestion = $sQuestion->questions()->save(
                new Set\Question([
                    'book_id'     => $this->file->book->set->id,
                    'question_id' => $pbQuestion->question_id,
                    'page'        => $sQuestion->page,
                    'sorter'      => $sorter,
                ])
            );
            $sAnswers = $sQuestion->answers->map(function($answer) {
                return new Set\Answer(['answer_id' => $answer->answer_id]);
            })->all();
            $pQuestion->answers()->saveMany($sAnswers);
            $pQuestion->load(['is', 'answers.is']);
            return $pQuestion;
        });

        return ['questions' => $pQuestions];
    }

}