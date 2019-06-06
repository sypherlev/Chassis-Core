<?php

namespace SypherLev\Chassis\Action;

use SypherLev\Chassis\Request\Web;

class WebAction extends AbstractAction
{
    private $request;

    public function __construct(Web $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }
}