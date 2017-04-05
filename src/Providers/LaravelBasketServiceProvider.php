<?php

namespace ChrisWillerton\LaravelBasket\Providers;

use Illuminate\Support\ServiceProvider;

class LaravelBasketServiceProvider extends ServiceProvider
{
	protected $defer = false;

	public function boot()
	{
		$request = $this->app->make('request');
		$config = $this->app->make('config');

		// Load in the helpers file
		require_once __DIR__ . '/../../helpers/helpers.php';

		// Publish the config files
		$this->publishes([
			__DIR__ . '/../../config/config.php' => config_path('laravel-basket/main.php'),
		]);

		// Bind LaravelBasket to the container
		$this->app->singleton('LaravelBasket', function($app)
		{
		    $driver = app()->make(Database::class, ['laravel-basket-key']);
		    $vat_rate = config('laravel-basket.main.vat_rate') ?: null;

		    $basket = app()->make(Basket::class, [$driver, $vat_rate]);

		    return $basket;
		});
	}

	public function register(){}
}
