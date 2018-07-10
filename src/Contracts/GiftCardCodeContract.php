<?php

namespace ChrisWillerton\LaravelBasket\Contracts;

interface GiftCardCodeContract
{
	public function getId();
	public function getCode();
	public function getBalance();
	
	public function canAdd(BasketContract $basket);
	public static function loadInstance($id);
}