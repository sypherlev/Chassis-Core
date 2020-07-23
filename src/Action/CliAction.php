<?php

namespace SypherLev\Chassis\Action;

use SypherLev\Chassis\Request\Cli;

class CliAction extends AbstractAction
{
    private $request;

    public function __construct(Cli $request)
    {
        $this->request = $request;
    }

    /**
     * @psalm-suppress MissingReturnType
     */
    public function getRequest()
    {
        return $this->request;
    }
}