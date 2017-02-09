<?php

namespace Chassis\Action;

use Chassis\Middleware\Collection;
use Chassis\Request\CliRequest;

class CliAction extends AbstractAction
{
    private $request;
    private $middleware;

    public function __construct(CliRequest $request, Collection $middleware)
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