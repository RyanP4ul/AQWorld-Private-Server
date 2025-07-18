<?php

namespace App\Route\Middleware;

interface MiddlewareInterface
{
    public function handle($request, $next);
}