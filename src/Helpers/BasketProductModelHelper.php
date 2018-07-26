<?php

namespace Engage\LaravelBasket\Helpers;

use Engage\LaravelBasket\Contracts\BasketContract;
use Engage\LaravelBasket\Contracts\BasketItemContract;

trait BasketProductModelHelper
{
	public function getId()
	{
		return $this->getKey();
	}

	public function getName()
	{
		return $this->{$this->identifier_key};
	}

	public function getPrice()
	{
		return $this->price;
	}

	public function getVatRate()
	{
		return false;
	}

	public function canAdd(BasketContract $basket, BasketItemContract $item, $quantity = false)
	{
		// Work out whether you can add the item here
		// 		- Is there enough stock?
		return true;
	}

	public static function loadInstance($id)
	{
		return static::find($id);
	}
}