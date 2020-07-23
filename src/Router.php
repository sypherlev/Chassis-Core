<?php

namespace SypherLev\Chassis;

use FastRoute;

class Router
{
    /* @var FastRoute\Dispatcher */
    private $dispatcher;

    private $routes;

    public function readyDispatcher() {
        $this->dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route->method, $route->pattern, $route->action);
            }
        });
    }

    public function trigger() : \stdClass {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
        // default to 404
        $ready = new \stdClass();
        $ready->http_code = 404;
        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                break;
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                // ... 405 Method Not Allowed
                $ready->http_code = 405;
                break;
            case FastRoute\Dispatcher::FOUND:
                // ... provisional 200 Found
                $ready = new \stdClass();
                $ready->action = $routeInfo[1];
                $ready->http_code = 200;
                $ready->segments = explode('/', ltrim($uri, '/'));
                $ready->placeholders = $routeInfo[2];
                break;
        }
        return $ready;
    }

    public function addRoute(string $method, string $pattern, string $classname) {
        $newroute = new \stdClass();
        $newroute->method = $method;
        $newroute->pattern = $pattern;
        $newroute->action = $classname;
        $this->routes[] = $newroute;
    }
}