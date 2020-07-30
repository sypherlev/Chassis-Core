<?php

namespace SypherLev\Chassis\Request;

use SypherLev\Chassis\Error\ChassisException;

class Web
{
    use WithEnvironmentVars;

    private $urlSegments = [];
    private $getparams = [];
    private $bodyparams = [];
    private $cookieparams = [];
    private $parsedfiles = [];
    private $placeholders = [];


    public function __construct()
    {
        $this->getparams = $_GET;
        $this->cookieparams = $_COOKIE;
        $this->bodyparams = $_POST;
        if(empty($this->bodyparams)) {
            $phpinput = json_decode(file_get_contents('php://input'), true);
            if (is_array($phpinput)) {
                $this->bodyparams = $phpinput;
            }
        }
        $this->parsedfiles = $_FILES;
        $this->setEnvironmentVars();
    }

    public function setSegmentData(array $segments)
    {
        $this->urlSegments = $segments;
    }

    public function setPlaceholderData(array $placeholders)
    {
        $this->placeholders = $placeholders;
    }

    public function fromSegments(int $position) : string
    {
        if (count($this->urlSegments) > $position) {
            return $this->urlSegments[$position];
        }
        throw new ChassisException("Cannot get segment ".$position." from request; position out of bounds");
    }

    public function fromPlaceholders(string $name) : string
    {
        if (isset($this->placeholders[$name])) {
            return $this->placeholders[$name];
        }
        throw new ChassisException("Cannot get placeholder ".$name." from URL; placeholder not present");
    }

    /**
     * @psalm-suppress MissingReturnType
     */
    public function fromQuery(string $name)
    {
        if (isset($this->getparams[$name])) {
            return $this->getparams[$name];
        }
        throw new ChassisException("Cannot get parameter ".$name." from URL; parameter not present");
    }

    /**
     * @psalm-suppress MissingReturnType
     */
    public function fromBody(string $name)
    {
        if (isset($this->bodyparams[$name])) {
            return $this->bodyparams[$name];
        }
        throw new ChassisException("Cannot get parameter ".$name." from request body; parameter not present");
    }

    public function fromCookie(string $name) : string
    {
        if (isset($this->cookieparams[$name])) {
            return $this->cookieparams[$name];
        }
        throw new ChassisException("Cannot get cookie ".$name."; cookie not present");
    }

    public function fromFiles(string $name) : array
    {
        if (isset($this->parsedfiles[$name])) {
            return $this->parsedfiles[$name];
        }
        throw new ChassisException("Cannot get file ".$name."; file not present");
    }
}