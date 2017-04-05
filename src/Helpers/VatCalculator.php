<?php

namespace ChrisWillerton\LaravelBasket\Helpers;

class VatCalculator
{
	protected $rate;

	public function __construct($rate)
	{
		$this->rate = $rate;
	}

	public function getRate()
	{
		return $this->rate;
	}

	public function setRate($rate)
	{
		$this->rate = $rate;

		return $this;
	}

	public function getVat($value, $rate = false)
	{
		return $value - $this->getWithoutVat($value, $rate);
	}

	public function getWithoutVat($value, $rate = false)
	{
		$rate = $rate !== false ? $rate : $this->rate;
		$vat_divisor = 1 + $rate / 100;

		return round($value / $vat_divisor, 2);
	}

	public function getWithVat($value, $rate = false)
	{
		return round(($rate !== false ? $rate : $this->rate) / 100 * $value, 2);
	}
}

