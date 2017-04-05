<?php

namespace ChrisWillerton\LaravelBasket\Contracts;

interface DeliveryOptionContract
{
	public function getId();
	public function getName();
	public function getDescription();
	public function getPrice();
	public function getFreeDeliveryThreshold();

	public function canAdd(BasketContract $basket);
	public static function loadInstance($id);
}