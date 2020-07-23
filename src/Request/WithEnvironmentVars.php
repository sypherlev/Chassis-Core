<?php

namespace SypherLev\Chassis\Request;

use SypherLev\Chassis\Error\ChassisException;

trait WithEnvironmentVars
{
    private $env_data;

    private function setEnvironmentVars() {
        $this->env_data = getenv();
    }

    /**
     * @psalm-suppress MissingReturnType
     */
    public function fromEnvironment(string $name) {
        if(isset($this->env_data[$name])) {
            return $this->env_data[$name];
        }
        else {
            throw new ChassisException("Cannot get parameter ".$name." from environment; parameter not present");
        }
    }
}