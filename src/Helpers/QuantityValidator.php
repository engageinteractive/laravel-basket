<?php

namespace ChrisWillerton\LaravelBasket\Helpers;

trait QuantityValidator
{
	protected function isInvalidQuantity($quantity)
	{
		return (!$this->isWhole($quantity) && $quantity < 0);
	}

	protected function isValidQuantity($quantity)
	{
		return ($this->isWhole($quantity) && $quantity >= 0);
	}

	protected function isWhole($number)
	{
		return (abs($number - round($number)) < 0.0001);
	}
}