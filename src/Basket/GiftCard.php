<?php

namespace ChrisWillerton\LaravelBasket\Basket;

use ChrisWillerton\LaravelBasket\Contracts\GiftCardCodeContract;

class GiftCardCode
{
	public $instance;

	public function __construct($code)
	{
		$this->instance = $code;
	}

	public function getId()
	{
		return $this->instance->getId();
	}

	public function getCode()
	{
		return $this->instance->getCode();
	}

	public function getBalance()
	{
		return $this->instance->getBalance();
	}
}