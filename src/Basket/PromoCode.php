<?php

namespace Engage\LaravelBasket\Basket;

use Engage\LaravelBasket\Contracts\PromoCodeContract;
use Engage\LaravelBasket\Contracts\BasketItemContract;
use Engage\LaravelBasket\Contracts\BasketContract;
use Engage\LaravelBasket\Helpers\MoneyFormatter;

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

	public function getItemDiscountAmount(BasketItemContract $item, BasketContract $basket)
	{
		return new MoneyFormatter($this->instance->getItemDiscountAmount($item, $basket));
	}

	public function hasFreeDelivery(BasketContract $basket)
	{
		return $this->instance->hasFreeDelivery($basket);
	}
}