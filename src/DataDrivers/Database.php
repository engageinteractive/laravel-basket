<?php

namespace ChrisWillerton\LaravelBasket\DataDrivers;

use ChrisWillerton\LaravelBasket\Contracts\DataDriverContract;

class Database implements DataDriverContract
{
	protected $key;

	protected $settings;
	protected $defaults = [
		'path'      =>   "/",
		'domain'    =>   false,
		'secure'    =>   false,
		'httponly'  =>   true
	];

	public function __construct($key, $settings = [])
	{
		$this->key = $key;
		$this->defaults['expire'] = time() + 259200;
		$this->settings = array_merge($this->defaults, $settings);
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
			$storage = BasketStorage::whereKey($basket_key)->first();

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
		$storage->ip_address = $_SERVER['REMOTE_ADDR'];
		$storage->expiry = $this->settings['expire'];

		$storage->save();

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
				$storage->delete();
			}
		}

		return true;
	}
}