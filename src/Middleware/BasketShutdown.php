<?php

namespace ChrisWillerton\LaravelBasket\Middleware;

use Closure;

class BasketShutdown
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        laravel_basket()->shutdown();

        return $response;
    }
}
