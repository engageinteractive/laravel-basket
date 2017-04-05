<?php

namespace ChrisWillerton\LaravelBasket\Contracts;

interface BasketProductContract
{
	public function getId();
	public function getName();
	public function getPrice();
	public function getVatRate();
	public function canAdd(BasketContract $basket, BasketItemContract $item, $quantity = false);
	public static function loadInstance($id);
}