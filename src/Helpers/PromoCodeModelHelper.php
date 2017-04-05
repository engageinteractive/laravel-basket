<?php

namespace ChrisWillerton\LaravelBasket\Helpers;

use ChrisWillerton\LaravelBasket\Contracts\BasketContract;

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

	public function getDiscountAmount(BasketContract $basket)
	{
		if ($this->getDiscountType() == 'percentage')
		{
			return $this->calculatePercentageDiscount($basket, $this->getDiscount());
		}

		if ($this->getDiscountType() == 'fixed')
		{
			return $this->calculateFixedDiscount($basket);
		}

		return 0;
	}

	public function getDiscountType()
	{
		return $this->discount_type;
	}

	public function hasFreeDelivery()
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

	protected function calculatePercentageDiscount(BasketContract $basket, $percentage)
	{
		$basket_total = $basket->getTotal()->getBasePrice();
		return round(($percentage / 100) * $basket_total);
	}

	protected function calculateFixedDiscount(BasketContract $basket)
	{
		return $this->getDiscount();
	}
}