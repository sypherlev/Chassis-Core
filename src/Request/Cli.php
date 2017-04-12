<?php

namespace SypherLev\Chassis\Request;

class Cli
{
    use WithEnvironmentVars;
    use WithMiddlewareVars;

    private $requestdata;

    public function __construct()
    {
        $this->setLineVars();
        $this->setEnvironmentVars();
    }

    public function getScriptName() {
        return $this->getRawData('scriptname');
    }

    public function getAction() {
        return $this->getRawData('action');
    }

    public function getAllLineVars() {
        return $this->getRawData('argv');
    }

    public function fromLineVars($int) {
        if(count($this->requestdata['argv']) > $int) {
            $count = 0;
            foreach($this->requestdata['argv'] as $value) {
                if($count == $int) {
                    return $value;
                }
                $count++;
            }
        }
        return null;
    }

    private function setLineVars() {
        global $argv;
        if(is_array($argv)) {
            $scriptname = array_shift($argv);
            $action = array_shift($argv);
            $this->insertData('scriptname', $scriptname);
            $this->insertData('action', $action);
            $this->insertData('argv', $argv);
        }
        else {
            throw(new \Exception("Can't initialize action: CLI arguments missing"));
        }
    }

    private function insertData($name, $input)
    {
        $this->requestdata[$name] = $input;
    }

    private function getRawData($name)
    {
        if(isset($this->requestdata[$name])) {
            return $this->requestdata[$name];
        }
        else {
            return null;
        }
    }
}