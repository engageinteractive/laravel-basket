<?php

namespace ChrisWillerton\LaravelBasket\Helpers;

trait DataEncrypter
{
	protected function encrypt($data)
	{
		return openssl_encrypt($data, 'AES-256-CBC', $this->encryption_key, OPENSSL_RAW_DATA, $this->iv);
	}

	protected function decrypt($data)
	{
		return openssl_decrypt($data, 'AES-256-CBC', $this->encryption_key, OPENSSL_RAW_DATA, $this->iv);
	}
}