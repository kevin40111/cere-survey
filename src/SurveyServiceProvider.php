<?php

namespace Cere\Survey;

use Illuminate\Support\ServiceProvider;
use Cere\Survey\Eloquent as SurveyORM;
use Cere\Survey\SurveyRepositoryInterface;
use Cere\Survey\SurveyRepository;
use Cere\Survey\DemoRepository;
use Cere\Survey\SurveySession;
use Auth;

class SurveyServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('cere/survey');

		include(__DIR__ . '/routes.php');

		include(__DIR__ . '/filters.php');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
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

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
