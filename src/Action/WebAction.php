<?php

namespace SypherLev\Chassis\Action;

use SypherLev\Chassis\Request\Web;
use SypherLev\Chassis\Middleware\Collection;

class WebAction extends AbstractAction
{
    private $request;
    private $middleware;

    public function __construct(Web $request, Collection $middleware)
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