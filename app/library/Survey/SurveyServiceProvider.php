<?php

namespace Plat\Survey;

use Plat\Eloquent\Survey as SurveyORM;
use Illuminate\Support\ServiceProvider;
use Plat\Survey\SurveyRepositoryInterface;
use Plat\Survey\SurveyRepository;
use Plat\Survey\DemoRepository;
use Plat\Survey\SurveySession;
use Auth;

class SurveyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(SurveyRepositoryInterface::class, function()
        {
            $type = $this->app->make('router')->input('type');
            $book_id = $this->app->make('router')->input('book_id');

            if ($type == 'demo') {
                $user_id = Auth::user()->id;

                $repository = new DemoRepository($book_id);
                if (!$repository->exist('answers')) {

                    $questions = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) {
                        return array_merge($carry, array_fetch($page->getQuestions(), 'id'));
                    }, []);

                    $repository->increment($user_id, array_fill_keys($questions, NULL));
                }
            }

            if ($type == 'survey') {
                $user_id = SurveySession::getHashId();
                $repository = new SurveyRepository($book_id);
            }

            return $repository;
        });
    }
}