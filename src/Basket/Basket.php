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
	protected $currency_code;
	protected $vat_rate = 0;
	protected $items = [];
	protected $delivery_option = false;
	protected $promo_code = false;
	protected $errors = [];
	protected $event_namespace;

	public function __construct(DataDriverContract $driver, $vat_rate = null)
	{
		$this->driver = $driver;

		if (!is_null($vat_rate))
		{
			$this->vat_rate = (float) $vat_rate;
		}

		$this->vat_calculator = new VatCalculator($this->vat_rate);

		$this->setCurrencyCode(config('laravel-basket.currency_code'));

		$this->event_namespace = config('laravel-basket.event_namespace');

		$this->retrieveBasket();

		event($this->event_namespace . '.constructed', $this);
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
			if ($this->delivery_option->hasFreeDelivery($this) || ($this->promo_code && $this->promo_code->hasFreeDelivery($this)))
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
			$running_discount = 0;

			// Work out item discounts first
			foreach ($this->getItems() as $item)
			{
				$running_discount += $this->promo_code->getItemDiscountAmount($item, $this)->getBasePrice();
			}

			$discount = new MoneyFormatter($running_discount);

			if ($discount->getBasePrice() >= $this->getPreDiscountTotal()->getBasePrice())
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

		event($this->event_namespace . '.addItem', [$this, $item]);

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

			event($this->event_namespace . '.itemUpdated', [$this, $item]);
		}

		return $this;
	}

	public function removeItem($id, $quantity = false)
	{
		if (isset($this->items[$id]))
		{
			$item = $this->items[$id];

			event($this->event_namespace . '.removeItem', [$this, $item]);

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

		event($this->event_namespace . '.empty', $this);

		return $this;
	}

	public function getTotalItems()
	{
		$quantity = 0;

		foreach ($this->items as $item)
		{
			$quantity += $item->getQuantity();
		};

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

		// Calculate delivery VAT first
		$total += (new VatCalculator(config('laravel-basket.delivery_vat_rate')))->getVat($this->getDeliveryPrice()->getBasePrice());

		// Retrieve VAT for each item
		foreach ($this->items as $item)
		{
			$total += $item->getUnformattedVatTotal();
		}

		return new MoneyFormatter(round($total));
	}

	public function getNetTotal()
	{
		$total = 0;

		foreach ($this->items as $item)
		{
			$total += $item->getUnformattedNetTotal();
		}

		return new MoneyFormatter(round($total));
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

	public function getPreDiscountTotal()
	{
		$total = 0;

		foreach ($this->items as $item)
		{
			$total += $item->getPreDiscountsTotal()->getBasePrice();
		}

		return new MoneyFormatter($total);
	}

	public function getGrandTotal()
	{
		$grand_total = 0;

		// Start with all items
		$grand_total += $this->getTotal()->getBasePrice();

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

		event($this->event_namespace . '.clear', $this);
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
		}

		event($this->event_namespace . '.retrieve', $this);

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

		event($this->event_namespace . '.store', $this);

		return $this;
	}
}
