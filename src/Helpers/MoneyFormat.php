<?php

namespace Engage\LaravelBasket\Helpers;

trait MoneyFormat
{
	public function getPriceAttribute()
	{
		return new MoneyFormatter($this->attributes['price']);
	}
}