<?php

namespace ChrisWillerton\LaravelBasket\Contracts;

interface GiftCardCodeContract
{
	public function getId();
	public function getCode();
	public function getBalance();
	public function getDiscount();
	public function getBalanceRemaining();
	public function updateBalance($balance);

	public function canAdd(BasketContract $basket);
	public static function loadInstance($id);
}