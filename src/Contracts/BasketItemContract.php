<?php

namespace Engage\LaravelBasket\Contracts;

interface BasketItemContract
{
	public function __construct(BasketContract $basket, BasketProductContract $product, $hash);

	public function getId();
	public function getName();

	public function getPrice();
	public function getVatRate();

	public function getCreatedAt();
	public function setCreatedAt($created_at);

	public function getQuantity();
	public function setQuantity($quantity);

	public function increaseQuantity($quantity);
	public function decreaseQuantity($quantity);

	public function getVatTotal();
	public function getNetTotal();
	public function getPreDiscountsTotal();
	public function getTotal();
	public function getDiscountTotal();

	public static function createItemHash(BasketProductContract $product);
}