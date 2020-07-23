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

    /**
     * @psalm-suppress MissingReturnType
     */
    public function fromOpts(string $optname, bool $required = false) {
        $cli_optname = $optname;
        if($required) {
            $cli_optname .= ":";
        }
        else {
            $cli_optname .= "::";
        }
        $opts = getopt($cli_optname);
        if($opts === false && $required) {
            throw new ChassisException("Required option named $optname not found");
        }
        if(isset($opts[$optname])) {
            return $opts[$optname];
        }
        return false;
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