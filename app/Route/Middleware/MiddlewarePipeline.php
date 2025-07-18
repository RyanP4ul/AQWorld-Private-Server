<?php

namespace App\Route\Middleware;


class MiddlewarePipeline
{
    private $middlewares = [];

    public function addMiddleware(MiddlewareInterface $middleware) : void
    {
        $this->middlewares[] = $middleware;
    }

    public function handle($request, $finalHandler)
    {
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            function ($next, $middleware) {
                return function ($request) use ($middleware, $next) {
                    return $middleware->handle($request, $next);
                };
            },
            $finalHandler
        );

        return $pipeline($request);
    }
}