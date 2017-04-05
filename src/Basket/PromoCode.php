<?php

namespace ChrisWillerton\LaravelBasket\Basket;

use ChrisWillerton\LaravelBasket\Contracts\PromoCodeContract;
use ChrisWillerton\LaravelBasket\Contracts\BasketContract;
use ChrisWillerton\LaravelBasket\Helpers\MoneyFormatter;

class PromoCode
{
	public $instance;

	public function __construct(PromoCodeContract $promo_code)
	{
		$this->instance = $promo_code;
	}

	public function getId()
	{
		return $this->instance->getId();
	}

	public function getCode()
	{
		return $this->instance->getCode();
	}

	public function getDescription()
	{
		return $this->instance->getDescription();
	}

	public function getDiscount()
	{
		return $this->instance->getDiscount();
	}

	public function getDiscountAmount(BasketContract $basket)
	{
		return new MoneyFormatter($this->instance->getDiscountAmount($basket));
	}

	public function getDiscountType()
	{
		return $this->instance->getDiscountType();
	}

	public function hasFreeDelivery()
	{
		return $this->instance->hasFreeDelivery();
	}
}