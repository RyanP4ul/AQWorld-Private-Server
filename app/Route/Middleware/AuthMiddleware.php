<?php

namespace App\Route\Middleware;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle($request, $next)
    {
        if (!isset($_SESSION["loggedIn"]) || !$_SESSION["loggedIn"])
        {
            header("Location: /");
            exit();
        }

        return $next($request);
    }
}