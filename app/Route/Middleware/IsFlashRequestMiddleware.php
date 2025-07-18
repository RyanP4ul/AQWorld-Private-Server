<?php

namespace App\Route\Middleware;

class IsFlashRequestMiddleware implements MiddlewareInterface
{

    public function handle($request, $next)
    {
        if (!isset($_SERVER["HTTP_X_REQUESTED_WITH"]) || stripos($_SERVER["HTTP_X_REQUESTED_WITH"], 'ShockwaveFlash') === false) {
            http_response_code(404);
            exit();
        }

        return $next($request);
    }
}