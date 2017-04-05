<?php

namespace ChrisWillerton\LaravelBasket\DataDrivers;

use Illuminate\Database\Eloquent\Model;

class BasketStorage extends Model
{
	protected $table = 'basket_storage';

	protected $fillable = [
		'key',
		'ip_address',
		'payload',
		'expiry'
	];
}