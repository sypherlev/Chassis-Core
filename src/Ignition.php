<?php

namespace SypherLev\Chassis;

use SypherLev\Chassis\Request\Cli;
use SypherLev\Chassis\Request\Web;
use SypherLev\Chassis\Action\ActionInterface;
use SypherLev\Chassis\Middleware\Collection;

class Ignition
{
    /** @var ActionInterface */
    protected $action;

    public function run(Router $router = null, Collection $middleware = null)
    {
        if (php_sapi_name() == "cli") {
            // In cli-mode; setup CLI Request and go to CLI action
            $request = new Cli();
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
            $route = $router->trigger();
            if (is_null($route)) {
                http_response_code(404);
                die('404 Page not found.');
            }
            if ($route === false) {
                http_response_code(405);
                die('405 HTTP method not allowed.');
            }
            if (!isset($route->action)) {
                http_response_code(500);
                die('500 Internal server error: Application action not found.');
            }
            $request = new Web();
            if(!empty($route->segments)) {
                $request->setSegmentData($route->segments);
            }
            if(!empty($route->placeholders)) {
                $request->setPlaceholderData($route->placeholders);
            }
            $actionname = $route->action;
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