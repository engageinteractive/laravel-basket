<?php

namespace ChrisWillerton\LaravelBasket\Basket;

use ChrisWillerton\LaravelBasket\Contracts\BasketContract;
use ChrisWillerton\LaravelBasket\Contracts\DataDriverContract;
use ChrisWillerton\LaravelBasket\Contracts\BasketProductContract;
use ChrisWillerton\LaravelBasket\Contracts\DeliveryOptionContract;
use ChrisWillerton\LaravelBasket\Contracts\PromoCodeContract;
use ChrisWillerton\LaravelBasket\Exceptions\BasketException;
use ChrisWillerton\LaravelBasket\Helpers\VatCalculator;
use ChrisWillerton\LaravelBasket\Helpers\MoneyFormatter;
use ChrisWillerton\LaravelBasket\Helpers\QuantityValidator;

class Basket implements BasketContract
{
	use QuantityValidator;

	protected $driver;
	protected $vat_calculator;
	protected $vat_rate = 0;
	protected $currency_code = 'GBP';
	protected $items = [];
	protected $delivery_option = false;
	protected $promo_code = false;
	protected $errors = [];

	public function __construct(DataDriverContract $driver, $vat_rate = null)
	{
		$this->driver = $driver;

		if (!is_null($vat_rate))
		{
			$this->vat_rate = (float) $vat_rate;
		}

		$this->vat_calculator = new VatCalculator($this->vat_rate);

		$this->retrieveBasket();
	}

	public function getDriver()
	{
		return $this->driver;
	}

	public function getVatRate()
	{
		return $this->vat_rate;
	}

	public function getCurrencyCode()
	{
		return $this->currency_code;
	}

	public function setCurrencyCode($iso_code)
	{
		$this->currency_code = $iso_code;

		return $this;
	}

	public function getDeliveryOption()
	{
		return $this->delivery_option;
	}

	public function setDeliveryOption(DeliveryOptionContract $option)
	{
		if ($this->silentTry('canAdd', $this, $option))
		{
			$this->delivery_option = new DeliveryOption($option);
		}

		return $this;
	}

	public function removeDeliveryOption()
	{
		$this->delivery_option = false;

		return $this;
	}

	public function getDeliveryPrice()
	{
		if ($this->delivery_option)
		{
			if ($this->promo_code && $this->promo_code->hasFreeDelivery())
			{
				return new MoneyFormatter(0);
			}

			if (
				($this->delivery_option->getFreeDeliveryThreshold() !== false) &&
				($this->getTotal()->getBasePrice() >= $this->delivery_option->getFreeDeliveryThreshold())
			)
			{
				return new MoneyFormatter(0);
			}

			return $this->delivery_option->getPrice();
		}

		return new MoneyFormatter(0);
	}

	public function getPromoCode()
	{
		return $this->promo_code;
	}

	public function setPromoCode(PromoCodeContract $promo_code)
	{
		if ($this->silentTry('canAdd', $this, $promo_code))
		{
			$this->promo_code = new PromoCode($promo_code);
		}

		return $this;
	}

	public function removePromoCode()
	{
		$this->promo_code = false;

		return $this;
	}

	public function getDiscount()
	{
		$promo_code = $this->getPromoCode();

		if ($promo_code)
		{
			if ($promo_code->getDiscountType() == 'percentage')
			{
				$discount = 0;

				array_walk($this->items, function($item) use (&$discount)
				{
					$discount += $item->getDiscountTotal()->getBasePrice();
				});

				$discount = new MoneyFormatter($discount);
			}
			else
			{
				$discount = $this->promo_code->getDiscountAmount($this);
			}

			if ($discount->getBasePrice() >= $this->getTotal()->getBasePrice())
			{
				return $this->getTotal();
			}

			return $discount;
		}

		return new MoneyFormatter(0);
	}

	public function addItem(BasketProductContract $product, $quantity = 1, $created_at = false)
	{
		$quantity = (int) $quantity;

		if ($this->isInvalidQuantity($quantity))
		{
			return $this;
		}

		$item_key = Item::createItemHash($product);

		if (isset($this->items[$item_key]))
		{
			$item = $this->items[$item_key];
		}
		else
		{
			$item = new Item($this, $product, $item_key);
			$this->items[$item_key] = $item;
		}

		if (!$this->silentTry('canAdd', [$this, $item, $quantity], $product))
		{
			$this->removeItem($item_key);
			return $this;
		}

		$item->increaseQuantity($quantity);

		if ($created_at)
		{
			$item->setCreatedAt($created_at);
		}

		return $this;
	}

	public function updateItemQuantity($id, $quantity)
	{
		$quantity = (int) $quantity;

		if ($this->isInvalidQuantity($quantity))
		{
			return $this;
		}

		if (isset($this->items[$id]))
		{
			$item = $this->items[$id];

			if ($this->silentTry('canAdd', [$this, $item, $quantity], $item->product))
			{
				$item->setQuantity($quantity);
			}

			if ($item->getQuantity() <= 0)
			{
				$this->removeItem($id);
			}
		}

		return $this;
	}

	public function removeItem($id, $quantity = false)
	{
		if (isset($this->items[$id]))
		{
			$item = $this->items[$id];

			if ($quantity)
			{
				$quantity = (int) $quantity;

				if ($this->isInvalidQuantity($quantity))
				{
					return $this;
				}

				$item->decreaseQuantity($quantity);

				if ($item->getQuantity() <= 0)
				{
					unset($this->items[$id]);
				}

				return $this;
			}

			unset($this->items[$id]);
		}

		return $this;
	}

	public function emptyItems()
	{
		$this->items = [];

		return $this;
	}

	public function getTotalItems()
	{
		$quantity = 0;

		array_walk($this->items, function($item) use (&$quantity)
		{
			$quantity += $item->getQuantity();
		});

		return $quantity;
	}

	public function getItems()
	{
		return $this->items;
	}

	public function getItem($id)
	{
		if (isset($this->items[$id]))
		{
			return $this->items[$id];
		}

		return false;
	}

	public function getVatTotal()
	{
		$total = 0;

		foreach ($this->items as $item)
		{
			$total += $item->getVatTotal()->getBasePrice();
		}

		$total += $this->vat_calculator->getVat($this->getDeliveryPrice()->getBasePrice());

		return new MoneyFormatter($total);
	}

	public function getNetTotal()
	{
		$total = 0;

		foreach ($this->items as $item)
		{
			$total += $item->getNetTotal()->getBasePrice();
		}

		return new MoneyFormatter($total);
	}

	public function getTotal()
	{
		$total = 0;

		foreach ($this->items as $item)
		{
			$total += $item->getTotal()->getBasePrice();
		}

		return new MoneyFormatter($total);
	}

	public function getGrandTotal()
	{
		$grand_total = 0;

		// Start with all items
		$grand_total += $this->getTotal()->getBasePrice();

		$promo_code = $this->getPromoCode();

		if ($promo_code)
		{
			if ($promo_code->getDiscountType() !== 'percentage')
			{
				// Remove any discounts
				$grand_total -= $this->getDiscount()->getBasePrice();
			}
		}

		// Add any delivery costs
		$grand_total += $this->getDeliveryPrice()->getBasePrice();

		return new MoneyFormatter($grand_total);
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function clear()
	{
		$this->emptyItems();
		$this->delivery_option = false;
		$this->promo_code = false;

		$this->driver->cleanupData();
	}

	public function shutdown()
	{
		$this->storeBasket();
	}

	protected function silentTry($method, $arguments = [], $object = false)
	{
		$arguments = is_array($arguments) ? $arguments : [$arguments];
		$object = $object ?: $this;

		try
		{
			return call_user_func_array([$object, $method], $arguments);
		}
		catch(BasketException $e)
		{
			$this->errors[] = $e->getMessage();
		}

		return false;
	}

	protected function retrieveBasket()
	{
		$basket = $this->driver->getData();

		if ($basket)
		{
			if ($basket['delivery_option'])
			{
				$classname = $basket['delivery_option']['classname'];
				$loaded_option = $classname::loadInstance($basket['delivery_option']['id']);

				if ($loaded_option)
				{
					$this->silentTry('setDeliveryOption', $loaded_option);
				}
			}

			if ($basket['promo_code'])
			{
				$classname = $basket['promo_code']['classname'];
				$loaded_code = $classname::loadInstance($basket['promo_code']['id']);

				if ($loaded_code)
				{
					$this->silentTry('setPromoCode', $loaded_code);
				}
			}

			foreach ($basket['items'] as $hash => $item)
			{
				$classname = $item['classname'];
				$loaded_item = $classname::loadInstance($item['id']);

				if ($loaded_item)
				{
					$this->silentTry('addItem', [
						$loaded_item,
						$item['quantity'],
						$item['created_at']
					]);
				}
			}
		}

		return $this;
	}

	protected function storeBasket()
	{
		$basket = [
			'items' => [],
			'delivery_option' => [],
			'promo_code' => []
		];

		if ($this->delivery_option)
		{
			$basket['delivery_option'] = [
				'id' => $this->delivery_option->getId(),
				'classname' => get_class($this->delivery_option->instance)
			];
		}

		if ($this->promo_code)
		{
			$basket['promo_code'] = [
				'id' => $this->promo_code->getId(),
				'classname' => get_class($this->promo_code->instance)
			];
		}

		foreach ($this->items as $hash => $item)
		{
			$basket['items'][$hash] = [
				'quantity' => $item->getQuantity(),
				'created_at' => $item->getCreatedAt(),
				'id' => $item->product->getId(),
				'classname' => get_class($item->product)
			];
		}

		$this->driver->setData($basket);

		return $this;
	}
}