<?php

namespace Engage\LaravelBasket\Helpers;

class MoneyFormatter
{
	protected $base_price;
	protected $unformatted_price;

	public function __construct($base_price)
	{
		$this->base_price = (int) $base_price;
		$this->unformatted_price = $this->base_price / 100;
		$this->setlocale(config('laravel-basket.locale'));
	}

	public function getBasePrice()
	{
		return $this->base_price;
	}

	public function getUnformattedPrice()
	{
		return $this->unformatted_price;
	}

	public function getPrice()
	{
		return number_format($this->getUnformattedPrice(), 2, '.', ',');
	}

	public function getFormattedPrice()
	{
		return $this->format(config('laravel-basket.money_format'), $this->getUnformattedPrice());
	}

	public function setlocale($locale)
	{
		setlocale(LC_MONETARY, $locale);

		return $this;
	}

	public function format($format, $number = false)
	{
		$price = $number ?: $this->getUnformattedPrice();
		return money_format($format, $price);
	}

	public function __toString()
	{
		return $this->getFormattedPrice();
	}
}