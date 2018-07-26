<?php

namespace Engage\LaravelBasket\Helpers;

use Engage\LaravelBasket\Contracts\BasketContract;

trait PromoCodeModelHelper
{
	public function getId()
	{
		return $this->getKey();
	}

	public function getCode()
	{
		return $this->code;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getDiscount()
	{
		return $this->discount;
	}

	public function hasFreeDelivery(BasketContract $basket)
	{
		return $this->free_delivery ? true : false;
	}

	public function canAdd(BasketContract $basket)
	{
		// Work out whether you can add the promo code here
		// 		- Is this a one time use code?
		// 		- Has this code expired?
		return true;
	}

	public static function loadInstance($id)
	{
		return static::find($id);
	}

	protected function calculatePercentageDiscount($total, $percentage)
	{
		return round(($percentage / 100) * $total);
	}
}