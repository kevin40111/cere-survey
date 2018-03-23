<?php

namespace Cere\Survey;

use Illuminate\Support\ServiceProvider;
use Cere\Survey\Eloquent as SurveyORM;
use Cere\Survey\Writer\WriterInterface;
use Cere\Survey\Writer\FieldWriter;
use Cere\Survey\Writer\SessionWriter;
use Cere\Survey\Auth\FieldUser;
use Auth;
use View;
use Config;

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
        Config::package('cere/survey', __DIR__ . '/../config');

		include(__DIR__ . '/routes.php');

        include(__DIR__ . '/filters.php');

        $this->app['config']['database.connections'] = array_merge(
			$this->app['config']['database.connections'], Config::get('survey::database.connections')
		);
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

                    $questions = SurveyORM\Book::find($book_id)->getQuestions()->fetch('id')->all();

                    $repository->increment(array_fill_keys($questions, NULL));
                }
            }

            if ($prefix == 'survey') {
                $book = SurveyORM\Book::find($book_id);
                $user = new FieldUser($book);
                $repository = new FieldWriter($book_id, $user);
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
