<?php


namespace SypherLev\Chassis\Error;


class ChassisException extends \Exception
{
    public function getFormattedMessage() : string {
        return $this->getMessage()." at line ".$this->getLine()." in ".$this->getFile();
    }
}