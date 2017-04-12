<?php

namespace SypherLev\Chassis\Request;

trait WithMiddlewareVars
{
    private $vars = [];

    public function addMiddlewareVar($name, $var)
    {
        if (!isset($this->vars[$name])) {
            $this->vars[$name] = $var;
        } else {
            throw new \Exception("Cannot set middleware variable $name; already exists");
        }
    }

    public function overwriteMiddlewareVar($name, $var)
    {
        $this->vars[$name] = $var;
    }

    public function removeMiddlewareVar($name)
    {
        unset($this->vars[$name]);
    }

    public function getAllMiddlewareVars() {
        return $this->vars;
    }

    public function getMiddlewareVar($name) {
        if (isset($this->vars[$name])) {
            return $this->vars[$name];
        } else {
            return null;
        }
    }
}