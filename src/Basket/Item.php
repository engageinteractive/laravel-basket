<?php

namespace ChrisWillerton\LaravelBasket\Basket;

use ChrisWillerton\LaravelBasket\Contracts\BasketContract;
use ChrisWillerton\LaravelBasket\Contracts\BasketItemContract;
use ChrisWillerton\LaravelBasket\Contracts\BasketProductContract;
use ChrisWillerton\LaravelBasket\Helpers\VatCalculator;
use ChrisWillerton\LaravelBasket\Helpers\MoneyFormatter;
use ChrisWillerton\LaravelBasket\Helpers\QuantityValidator;

class Item implements BasketItemContract
{
	use QuantityValidator;

	public $product;

	protected $basket;
	protected $hash;
	protected $created_at;
	protected $vat_rate = 0;
	protected $quantity = 0;
	protected $vat_calculator;

	public function __construct(BasketContract $basket, BasketProductContract $product, $hash)
	{
		$this->basket = $basket;
		$this->product = $product;
		$this->hash = $hash;
		$this->created_at = time();

		$basket_rate = $this->basket->getVatRate();
		$this->vat_rate = $product->getVatRate() !== false ? $product->getVatRate() : $basket_rate;
		$this->vat_calculator = new VatCalculator($this->vat_rate);
	}

	public function getId()
	{
		return $this->hash;
	}

	public function getName()
	{
		return $this->product->getName();
	}

	public function getPrice()
	{
		return new MoneyFormatter($this->product->getPrice());
	}

	public function getVatRate()
	{
		return $this->vat_rate;
	}

	public function getCreatedAt()
	{
		return $this->created_at;
	}

	public function setCreatedAt($created_at)
	{
		$this->created_at = $created_at;
		return $this;
	}

	public function getQuantity()
	{
		return $this->quantity;
	}

	public function setQuantity($quantity)
	{
		$quantity = (int) $quantity;

		if ($this->isInvalidQuantity($quantity))
		{
			return $this;
		}

		$this->quantity = $quantity;

		return $this;
	}

	public function increaseQuantity($quantity)
	{
		$quantity = (int) $quantity;

		if ($this->isInvalidQuantity($quantity))
		{
			return $this;
		}

		$this->quantity += $quantity;

		return $this;
	}

	public function decreaseQuantity($quantity)
	{
		$quantity = (int) $quantity;

		if ($this->isInvalidQuantity($quantity))
		{
			return $this;
		}

		$this->quantity -= $quantity;

		return $this;
	}

	public function getVatTotal()
	{
		return new MoneyFormatter($this->vat_calculator->getVat($this->getTotal()->getBasePrice()));
	}

	public function getNetTotal()
	{
		return new MoneyFormatter($this->vat_calculator->getWithoutVat($this->getTotal()->getBasePrice()));
	}

	public function getPreDiscountsTotal()
	{
		return new MoneyFormatter($this->getPrice()->getBasePrice() * $this->getQuantity());
	}

	public function getTotal()
	{
		$promo_code = $this->basket->getPromoCode();

		if ($promo_code && ($promo_code->getDiscountType() == 'percentage'))
		{
			return new MoneyFormatter(
				round($this->percentageDiscountedPrice(
					$this->getPrice()->getBasePrice(),
					$promo_code->getDiscount()
				) * $this->getQuantity())
			);
		}

		return new MoneyFormatter($this->getPrice()->getBasePrice() * $this->getQuantity());
	}

	public function getDiscountTotal()
	{
		return new MoneyFormatter($this->getPreDiscountsTotal()->getBasePrice() - $this->getTotal()->getBasePrice());
	}

	public static function createItemHash(BasketProductContract $product)
	{
		return md5(get_class($product) . $product->getId());
	}

	protected function percentageDiscountedPrice($price, $percentage_discount)
	{
		return $price - $this->calculatePercentageDiscount($price, $percentage_discount);
	}

	protected function calculatePercentageDiscount($price, $percentage_discount)
	{
		return ($percentage_discount / 100) * $price;
	}
}
