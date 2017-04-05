<?php

if (!function_exists('laravel_basket'))
{
	function laravel_basket($driver = null)
	{
		return app()->make('LaravelBasket');
	}
}