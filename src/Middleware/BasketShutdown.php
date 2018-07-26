<?php

namespace Engage\LaravelBasket\Middleware;

use Closure;

class BasketShutdown
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        app('LaravelBasket')->shutdown();

        return $response;
    }
}
