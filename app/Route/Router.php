<?php

namespace App\Route;

use App\Route\Middleware\MiddlewareInterface;
use App\Route\Middleware\MiddlewarePipeline;
use FastRoute\Dispatcher;

class Router
{

    private Dispatcher $dispatcher;
    private array $routeMiddlewares = [];
    private array $groupMiddlewares = [];
    private array $routesInGroups = [];

    public function __construct($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function addRouteMiddleware($route, MiddlewareInterface $middleware) : void
    {
        $this->routeMiddlewares[$route][] = $middleware;
    }

    public function addGroupMiddleware($group, MiddlewareInterface $middleware) : void
    {
        $this->groupMiddlewares[$group][] = $middleware;
    }

    public function group($group, $routes) : void
    {
        $this->routesInGroups[$group] = $routes;
    }

    public function dispatch() : void
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        $uri = rawurldecode($uri);

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                http_response_code(404);
                echo '404 Not Found';
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                http_response_code(405);
                echo '405 Method Not Allowed';
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                $route = rtrim($uri, '/');

                $middlewares = $this->getRouteMiddlewares($route);

                $pipeline = new MiddlewarePipeline();
                foreach ($middlewares as $middleware) {
                    $pipeline->addMiddleware($middleware);
                }

                $finalHandler = function ($request) use ($handler, $vars) {
                    [$controller, $method] = $handler;
                    $controllerInstance = new $controller();
                    return $controllerInstance->$method($request, $vars);
                };

                $pipeline->handle($_REQUEST, $finalHandler);
                break;
        }
    }

    private function getRouteMiddlewares($route) : array
    {
        $middlewares = [];

        foreach ($this->routesInGroups as $group => $routes) {
            if (in_array($route, $routes)) {
                $middlewares = array_merge($middlewares, $this->groupMiddlewares[$group] ?? []);
            }
        }

        return array_merge($middlewares, $this->routeMiddlewares[$route] ?? []);
    }
}