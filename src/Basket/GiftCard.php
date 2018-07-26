<?php

namespace Engage\LaravelBasket\Basket;

use Engage\LaravelBasket\Contracts\GiftCardCodeContract;
use Engage\LaravelBasket\Contracts\BasketContract;
use Engage\LaravelBasket\Helpers\MoneyFormatter;

class GiftCardCode
{
	public $instance;

	public function __construct(GiftCardCodeContract $code)
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

	public function getDiscount()
	{
		return $this->instance->getDiscount();
	}

	public function getBalanceRemaining()
	{
		return $this->instance->getBalanceRemaining();
	}

	public function updateBalance($balance)
	{
		return $this->instance->updateBalance($balance);
	}

}