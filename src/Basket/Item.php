<?php

namespace Engage\LaravelBasket\Basket;

use Engage\LaravelBasket\Contracts\BasketContract;
use Engage\LaravelBasket\Contracts\BasketItemContract;
use Engage\LaravelBasket\Contracts\BasketProductContract;
use Engage\LaravelBasket\Helpers\VatCalculator;
use Engage\LaravelBasket\Helpers\MoneyFormatter;
use Engage\LaravelBasket\Helpers\QuantityValidator;

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

	public function getUnformattedVatTotal()
	{
		return $this->vat_calculator->getVat($this->getTotal()->getBasePrice());
	}

	public function getVatTotal()
	{
		return new MoneyFormatter(round($this->getUnformattedVatTotal()));
	}

	public function getUnformattedNetTotal()
	{
		return $this->vat_calculator->getWithoutVat($this->getTotal()->getBasePrice());
	}

	public function getNetTotal()
	{
		return new MoneyFormatter(round($this->getUnformattedNetTotal()));
	}

	public function getPreDiscountsTotal()
	{
		return new MoneyFormatter($this->getPrice()->getBasePrice() * $this->getQuantity());
	}

	public function getTotal()
	{
		$total = $this->getPrice()->getBasePrice() * $this->getQuantity();
		$promo_code = $this->basket->getPromoCode();

		if ($promo_code)
		{
			$discount = $promo_code->getItemDiscountAmount($this, $this->basket)->getBasePrice();

			return new MoneyFormatter($total - $discount);
		}

		return new MoneyFormatter($total);
	}

	public function getDiscountTotal()
	{
		return new MoneyFormatter($this->getPreDiscountsTotal()->getBasePrice() - $this->getTotal()->getBasePrice());
	}

	public static function createItemHash(BasketProductContract $product)
	{
		return md5(get_class($product) . $product->getId());
	}
}
