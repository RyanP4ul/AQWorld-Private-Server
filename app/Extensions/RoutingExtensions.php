<?php

namespace App\Extensions;

use App\config;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RoutingExtensions extends AbstractExtension
{

    private array $routes;

    public function __construct($routes)
    {
        $this->routes = $routes;
    }

    public function getFunctions() : array
    {
        return [
            new TwigFunction('route', [$this, 'getUrl']),
        ];
    }

    public function getUrl($name)
    {
        return $name != "current" && array_key_exists($name, $this->routes) ? $this->routes[$name][1] : parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

}