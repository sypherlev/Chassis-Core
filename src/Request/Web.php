<?php

namespace SypherLev\Chassis\Request;

class Web
{
    use WithEnvironmentVars;
    use WithMiddlewareVars;

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
        if(is_array($phpinput)) {
            $this->bodyparams = array_merge($bodyparams, $phpinput);
        }
        else {
            $this->bodyparams = $bodyparams;
        }
        $this->parsedfiles = $this->parseFiles();
        $this->setEnvironmentVars();
    }

    public function setSegmentData($segments) {
        $this->urlSegments = $segments;
    }

    public function setPlaceholderData($placeolders) {
        $this->placeholders = $placeolders;
    }

    public function fromSegments($position) {
        if(count($this->urlSegments) > $position) {
            return $this->urlSegments[$position];
        }
        return null;
    }

    public function fromPlaceholders($name) {
        if(isset($this->placeholders[$name])) {
            return $this->placeholders[$name];
        }
        return null;
    }

    public function fromQuery($name) {
        if(isset($this->getparams[$name])) {
            return $this->getparams[$name];
        }
        return null;
    }

    public function fromBody($name) {
        if(isset($this->bodyparams[$name])) {
            return $this->bodyparams[$name];
        }
        return null;
    }

    public function fromCookie($name) {
        if(isset($this->cookieparams[$name])) {
            return $this->cookieparams[$name];
        }
        return null;
    }

    public function fromFileStack() {
        if(count($this->parsedfiles) > 0) {
            return array_shift($this->parsedfiles);
        }
        return null;
    }

    private function parseFiles() {
        $files = array();
        foreach ($_FILES['files']['name'] as $num_key => $dummy) {
            foreach ($_FILES['files'] as $txt_key => $dummy) {
                $files[$num_key][$txt_key] = $_FILES['files'][$txt_key][$num_key];
            }
        }
        return $files;
    }
}