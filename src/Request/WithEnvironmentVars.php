<?php

namespace SypherLev\Chassis\Request;

trait WithEnvironmentVars
{
    private $env_data;

    private function setEnvironmentVars() {
        $this->env_data = getenv();
    }

    public function fromEnvironment($name) {
        if(isset($this->env_data[$name])) {
            return $this->env_data[$name];
        }
        else {
            return null;
        }
    }
}