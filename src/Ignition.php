<?php

namespace SypherLev\Chassis;

use SypherLev\Chassis\Action\ActionInterface;
use SypherLev\Chassis\Middleware\Collection;
use SypherLev\Chassis\Request\CliRequest;
use SypherLev\Chassis\Request\WebRequest;
use League\Container\Container;

class Ignition
{
    /** @var ActionInterface */
    protected $action;

    public function run(Router $router = null, Collection $middleware = null)
    {
        if (php_sapi_name() == "cli") {
            // In cli-mode; setup CLI Request and go to CLI action
            $request = new CliRequest();
            $methodname = null;
            $actionname = $request->getAction();
            $possiblemethod = explode(':', $actionname);
            if (count($possiblemethod) > 1) {
                $actionname = $possiblemethod[0];
                $methodname = $possiblemethod[1];
            }
            $this->action = new $actionname($request, $middleware);
            $this->action->setup($methodname);
            if ($methodname != null && $this->action->isExecutable()) {
                $this->action->execute();
            }

        } else {
            // Not in cli-mode; divert to the router
            // initial check for whether to redirect to secure page
            if(isset($_ENV['alwaysssl']) && $_ENV['alwaysssl'] === 'true' && !$this->isSecure()) {
                $secureredirect = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                header("Location: $secureredirect");
                exit;
            }
            if($router == null) {
                http_response_code(500);
                die('500 Internal server error: Application router is not available.');
            }
            $router->readyDispatcher();
            $response = $router->trigger();
            if (is_null($response)) {
                http_response_code(404);
                die('404 Page not found.');
            }
            if ($response === false) {
                http_response_code(405);
                die('405 HTTP method not allowed.');
            }
            if (!isset($response->action)) {
                http_response_code(500);
                die('500 Internal server error: Application action not found.');
            }
            $request = new WebRequest();
            if(!empty($response->segments)) {
                $request->setSegmentData($response->segments);
            }
            $actionname = $response->action;
            $methodname = null;
            $possiblemethod = explode(':', $actionname);
            if (count($possiblemethod) > 1) {
                $actionname = $possiblemethod[0];
                $methodname = $possiblemethod[1];
            }
            $this->action = new $actionname($request, $middleware);
            $this->action->setup($methodname);
            if ($methodname != null && $this->action->isExecutable()) {
                $this->action->execute();
            }
        }
    }

    private function isSecure() {
        $isSecure = false;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $isSecure = true;
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
            $isSecure = true;
        }
        return $isSecure;
    }
}