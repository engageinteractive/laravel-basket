<?php

namespace ChrisWillerton\LaravelBasket\Basket;

use ChrisWillerton\LaravelBasket\Contracts\DeliveryOptionContract;
use ChrisWillerton\LaravelBasket\Helpers\MoneyFormatter;

class DeliveryOption
{
	public $instance;

	public function __construct(DeliveryOptionContract $delivery_option)
	{
		$this->instance = $delivery_option;
	}

	public function getId()
	{
		return $this->instance->getId();
	}

	public function getName()
	{
		return $this->instance->getName();
	}

	public function getDescription()
	{
		return $this->instance->getDescription();
	}

	public function getPrice()
	{
		return new MoneyFormatter($this->instance->getPrice());
	}

	public function getFreeDeliveryThreshold()
	{
		return $this->instance->getFreeDeliveryThreshold();
	}
}