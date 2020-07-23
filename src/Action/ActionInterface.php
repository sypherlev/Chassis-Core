<?php

namespace SypherLev\Chassis\Action;

use SypherLev\Chassis\Response\ResponseInterface;

interface ActionInterface
{
    public function setup(string $methodname);
    public function isExecutable() : bool;
    public function disableExecution(ResponseInterface $response);
    public function enableExecution();
    public function execute();
    public function getRequest();
    public function init();
}