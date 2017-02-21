<?php

namespace SypherLev\Chassis\Request;


interface RequestInterface
{
    public function insertData($name, $input);
    public function getRawData($name);
}