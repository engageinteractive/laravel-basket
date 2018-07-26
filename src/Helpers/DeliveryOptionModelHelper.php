<?php

namespace Engage\LaravelBasket\Helpers;

use Engage\LaravelBasket\Contracts\BasketContract;

trait DeliveryOptionModelHelper
{
	public function getId()
	{
		return $this->getKey();
	}

	public function getName()
	{
		return $this->{$this->identifier_key};
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getPrice()
	{
		return $this->price;
	}

	public function getFreeDeliveryThreshold()
	{
		return !is_null($this->free_delivery_threshold) ? $this->free_delivery_threshold : false;
	}

	public function hasFreeDelivery(BasketContract $basket)
	{
		// Work out whether your delivery option has free delivery here
		return false;
	}

	public function canAdd(BasketContract $basket)
	{
		// Work out whether you can add this delivery option here
		return true;
	}

	public static function loadInstance($id)
	{
		return static::find($id);
	}
}