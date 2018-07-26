<?php

namespace Engage\LaravelBasket\Helpers;

use Engage\LaravelBasket\Contracts\BasketContract;

trait GiftCardCodeModelHelper
{
	public function getId()
	{
		return $this->getKey();
	}

	public function getCode()
	{
		return $this->code;
	}

	public function getBalance()
	{
		return $this->balance;
	}

	public function getDiscount()
	{
		return $this->discount;
	}

	public function getBalanceRemaining()
	{
		return $this->balanceRemaining;
	}

	public function updateBalance($balance)
	{
		$this->balance = $balance;
		
		return $this->balance;
	}

	public function canAdd(BasketContract $basket)
	{
		// Work out whether you can add the gift card code here
		// 		- Has this code run out of balance?
		// 		- Has this code expired?
		return true;
	}

	public static function loadInstance($id)
	{
		return static::find($id);
	}
}