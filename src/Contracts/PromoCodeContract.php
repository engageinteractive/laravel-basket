<?php

namespace ChrisWillerton\LaravelBasket\Contracts;

interface PromoCodeContract
{
	public function getId();
	public function getCode();
	public function getDescription();

	public function getDiscount();
	public function getDiscountAmount(BasketContract $basket);
	public function getDiscountType();
	public function hasFreeDelivery(BasketContract $basket);

	public function canAdd(BasketContract $basket);
	public static function loadInstance($id);
}