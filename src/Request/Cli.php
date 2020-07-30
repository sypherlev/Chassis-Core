<?php

namespace SypherLev\Chassis\Request;

use SypherLev\Chassis\Error\ChassisException;

class Cli
{
    use WithEnvironmentVars;

    private $requestdata;

    public function __construct()
    {
        $this->setLineVars();
        $this->setEnvironmentVars();
    }

    public function getScriptName() : string {
        return $this->getRawData('scriptname');
    }

    public function getAction() : string {
        return $this->getRawData('action');
    }

    public function getAllLineVars() : array {
        return $this->getRawData('argv');
    }

    /**
     * @psalm-suppress MissingReturnType
     */
    public function fromLineVars(int $int) {
        if(count($this->requestdata['argv']) > $int) {
            $count = 0;
            foreach($this->requestdata['argv'] as $value) {
                if($count == $int) {
                    return $value;
                }
                $count++;
            }
        }
        throw new ChassisException("No line var found at position ".$int);
    }

    private function setLineVars() {
        global $argv;
        $scriptname = array_shift($argv);
        $action = array_shift($argv);
        $this->insertData('scriptname', $scriptname);
        $this->insertData('action', $action);
        $this->insertData('argv', $argv);
    }

    /**
     * @psalm-suppress MissingParamType
     */
    private function insertData(string $name, $input)
    {
        $this->requestdata[$name] = $input;
    }

    /**
     * @psalm-suppress MissingReturnType
     */
    private function getRawData(string $name)
    {
        return $this->requestdata[$name];
    }
}