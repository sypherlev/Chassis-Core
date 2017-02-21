<?php

namespace SypherLev\Chassis\Action;

use SypherLev\Chassis\Middleware\Collection;
use SypherLev\Chassis\Request\WebRequest;

class WebAction extends AbstractAction
{
    private $request;
    private $middleware;

    public function __construct(WebRequest $request, Collection $middleware)
    {
        $this->request = $request;
        $this->middleware = $middleware;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getMiddleware()
    {
        return $this->middleware;
    }
}