<?php

namespace SypherLev\Chassis\Action;

use SypherLev\Chassis\Error\ChassisException;
use SypherLev\Chassis\Response\ResponseInterface;

abstract class AbstractAction implements ActionInterface
{
    private $executable = true;
    private $methodname;

    public function setup(string $methodname)
    {
        $this->init();
        $this->methodname = $methodname;
    }

    public function execute()
    {
        if (!empty($this->methodname) && method_exists($this, $this->methodname)) {
            $this->{$this->methodname}();
        } else {
            throw new ChassisException("Error: method " . $this->methodname . " in " . get_called_class() . " does not exist");
        }
    }

    public function isExecutable() : bool
    {
        return $this->executable;
    }

    public function disableExecution(ResponseInterface $response)
    {
        $this->executable = false;
        $response->out();
    }

    public function enableExecution()
    {
        $this->executable = true;
    }

    public function init()
    {

    }
}