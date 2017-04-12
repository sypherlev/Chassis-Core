<?php

namespace SypherLev\Chassis\Request;

use GuzzleHttp\Psr7\ServerRequest;

class Web
{
    use WithEnvironmentVars;
    use WithMiddlewareVars;

    private $request;

    private $urlSegments = [];
    private $getparams = [];
    private $bodyparams = [];
    private $cookieparams = [];
    private $parsedfiles = [];


    public function __construct()
    {
        $this->request = ServerRequest::fromGlobals();
        $this->getparams = $this->request->getQueryParams();
        $this->cookieparams = $this->request->getCookieParams();
        $this->bodyparams = $this->request->getParsedBody();
        $this->parsedfiles = $this->request->getUploadedFiles();

        $this->setEnvironmentVars();
    }

    public function getPSR7Request() {
        return $this->request;
    }

    public function setSegmentData($segments) {
        $this->urlSegments = $segments;
    }

    public function fromSegments($position) {
        if(count($this->urlSegments) > $position) {
            return $this->urlSegments[$position];
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

    public function fromFiles($name) {
        if(isset($this->parsedfiles[$name])) {
            return $this->parsedfiles[$name];
        }
        return null;
    }
}