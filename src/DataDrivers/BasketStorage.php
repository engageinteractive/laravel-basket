<?php

namespace Engage\LaravelBasket\DataDrivers;

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

    public function __construct(array $attributes = [])
    {
        $additional_fillable = config('laravel-basket.storage_model_additional_fillable');

        if ($additional_fillable)
        {
            $this->fillable = array_merge($this->fillable, $additional_fillable);
        }
    }
}