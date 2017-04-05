<?php

namespace ChrisWillerton\LaravelBasket\Contracts;

interface DataDriverContract
{
	public function getData();
	public function setData($data);
	public function cleanupData();
}