<?php

namespace ChrisWillerton\LaravelBasket\Providers;

use Illuminate\Support\ServiceProvider;
use ChrisWillerton\LaravelBasket\DataDrivers\Database;
use ChrisWillerton\LaravelBasket\Basket\Basket;

class LaravelBasketServiceProvider extends ServiceProvider
{
	protected $defer = false;

	public function boot()
	{
		$request = $this->app->make('request');

		// Publish the config files
		$this->publishes([
			__DIR__ . '/../../config/config.php' => config_path('laravel-basket.php'),
		], 'laravel-basket-config');

		// Publish the migrations
		$this->publishes([
	        __DIR__ . '/../../migrations/' => database_path('migrations')
	    ], 'laravel-basket-migrations');
	}

	public function register()
	{
		// Bind LaravelBasket to the container
		$this->app->singleton('LaravelBasket', function($app)
		{
			$config = config('laravel-basket');
		    $vat_rate = $config['vat_rate'] ?: null;

		    $basket = new Basket(new Database($config['cookie_key']), $vat_rate);

		    return $basket;
		});
	}
}
