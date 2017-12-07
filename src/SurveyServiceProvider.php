<?php

namespace Cere\Survey;

use Illuminate\Support\ServiceProvider;
use Cere\Survey\Eloquent as SurveyORM;
use Cere\Survey\Writer\WriterInterface;
use Cere\Survey\Writer\FieldWriter;
use Cere\Survey\Writer\SessionWriter;
use Auth;
use View;

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

		View::addNamespace('survey', __DIR__.'/../resources/views');

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
        $this->app->bind(WriterInterface::class, function()
        {
            $prefix = $this->app->make('router')->getCurrentRoute()->getPrefix();
            $book_id = $this->app->make('router')->input('book_id');

            if ($prefix == 'surveyDemo') {

                $repository = new SessionWriter($book_id);
                if (!$repository->exist('answers')) {

                    $questions = SurveyORM\Book::find($book_id)->sortByPrevious(['childrenNodes'])->childrenNodes->reduce(function ($carry, $page) {
                        return array_merge($carry, array_fetch($page->getQuestions(), 'id'));
                    }, []);

                    $repository->increment(array_fill_keys($questions, NULL));
                }
            }

            if ($prefix == 'survey') {
                $repository = new FieldWriter($book_id);
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
