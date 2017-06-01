<?php

namespace ChrisWillerton\LaravelBasket\DataDrivers;

use ChrisWillerton\LaravelBasket\Contracts\DataDriverContract;

class Database implements DataDriverContract
{
	protected $key;
	protected $settings;
	protected $event_namespace;

	public function __construct($key)
	{
		$this->key = $key;
		$this->settings = config('laravel-basket.cookie_settings');
		$this->settings['expire'] = time() + config('laravel-basket.lifetime');
		$this->event_namespace = config('laravel-basket.event_namespace');
	}

	protected function getCookie()
	{
		return (isset($_COOKIE[$this->key]) && $_COOKIE[$this->key] != "") ? $_COOKIE[$this->key] : false;
	}

	protected function setCookie()
	{
		$cookie_data = uniqid('', true);

		setcookie(
			$this->key,
			$cookie_data,
			$this->settings['expire'],
			$this->settings['path'],
			$this->settings['domain'],
			$this->settings['secure'],
			$this->settings['httponly']
		);

		return $cookie_data;
	}

	public function getData()
	{
		$basket_key = $this->getCookie();

		if ($basket_key)
		{
			$storage = BasketStorage::where('key', $basket_key)->first();
			return $storage ? json_decode($storage->payload, true) : [];
		}

		return [];
	}

	public function setData($data)
	{
		$basket_key = $this->getCookie();

		if (!$basket_key)
		{
			$basket_key = $this->setCookie();
		}

		$storage = BasketStorage::firstOrNew([
			'key' => $basket_key
		]);

		$storage->payload = json_encode($data);
		$storage->ip_address = request()->ip();
		$storage->expiry = $this->settings['expire'];

		event($this->event_namespace . '.savingData', $storage);

		$storage->save();

		event($this->event_namespace . '.savedData', $storage);

		return $this;
	}

	public function cleanupData()
	{
		$basket_key = $this->getCookie();

		if ($basket_key)
		{
			$storage = BasketStorage::whereKey($basket_key)->first();

			if ($storage)
			{
				event($this->event_namespace . '.cleaningData', $storage);

				$storage->delete();

				event($this->event_namespace . '.cleanedData', $storage);
			}
		}

		return true;
	}
}