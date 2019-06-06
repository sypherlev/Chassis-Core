<?php

namespace SypherLev\Chassis\Request;

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
        $bodyparams = $_POST;
        $phpinput = json_decode(file_get_contents('php://input'), true);
        if (is_array($phpinput)) {
            $this->bodyparams = array_merge($bodyparams, $phpinput);
        } else {
            $this->bodyparams = $bodyparams;
        }
        $this->parsedfiles = $_FILES;
        $this->setEnvironmentVars();
    }

    public function setSegmentData($segments)
    {
        $this->urlSegments = $segments;
    }

    public function setPlaceholderData($placeolders)
    {
        $this->placeholders = $placeolders;
    }

    public function fromSegments($position)
    {
        if (count($this->urlSegments) > $position) {
            return $this->urlSegments[$position];
        }
        return null;
    }

    public function fromPlaceholders($name)
    {
        if (isset($this->placeholders[$name])) {
            return $this->placeholders[$name];
        }
        return null;
    }

    public function fromQuery($name)
    {
        if (isset($this->getparams[$name])) {
            return $this->getparams[$name];
        }
        return null;
    }

    public function fromBody($name)
    {
        if (isset($this->bodyparams[$name])) {
            return $this->bodyparams[$name];
        }
        return null;
    }

    public function fromCookie($name)
    {
        if (isset($this->cookieparams[$name])) {
            return $this->cookieparams[$name];
        }
        return null;
    }

    public function fromFiles($name)
    {
        if (isset($this->parsedfiles[$name])) {
            return $this->parsedfiles[$name];
        }
        return null;
    }
}