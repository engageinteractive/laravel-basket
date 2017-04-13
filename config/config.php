<?php

return [

    'locale' => 'en_GB.UTF-8',

    'money_format' => '%.2n',

    'currency_code' => 'GBP',

	'vat_rate' => 20,

    'cookie_key' => 'laravel_basket',

	'lifetime' => 259200, // How many seconds? Defaults to 3 days

    'cookie_settings' => [
        'path'      =>   "/",
        'domain'    =>   false,
        'secure'    =>   false,
        'httponly'  =>   true
    ]

];