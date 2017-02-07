<?php

namespace Chassis\Action;

use Chassis\Middleware\Collection;
use Chassis\Request\WebRequest;
use League\Container\Container;

class WebAction extends AbstractAction
{
    private $request;
    private $container;
    private $middleware;

    public function __construct(WebRequest $request, Container $container, Collection $middleware)
    {
        $this->request = $request;
        $this->container = $container;
        $this->middleware = $middleware;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getMiddleware()
    {
        return $this->middleware;
    }
}