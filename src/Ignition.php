<?php

namespace SypherLev\Chassis;

use SypherLev\Chassis\Error\ChassisException;
use SypherLev\Chassis\Request\Cli;
use SypherLev\Chassis\Request\Web;
use SypherLev\Chassis\Action\ActionInterface;

class Ignition
{
    public function run(Router $router = null, bool $isCli = false)
    {
        if ($isCli) {
            // In cli-mode; setup CLI Request and go to CLI action
            $request = new Cli();
            $methodname = null;
            $actionname = $request->getAction();
            $possiblemethod = explode(':', $actionname);
            if (count($possiblemethod) > 1) {
                $actionname = $possiblemethod[0];
                $methodname = $possiblemethod[1];
            }

            /** @var ActionInterface */
            $action = new $actionname($request);
            if(is_null($methodname)) {
                $methodname = 'index';
            }
            $action->setup($methodname);
            if ($methodname != null && $action->isExecutable()) {
                $action->execute();
            }

        } else {
            // Not in cli-mode; divert to the router
            // initial check for whether to redirect to secure page
            if (!isset($_SERVER['HTTP_HOST'])) {
                // do not support requests without a host
                http_response_code(500);
                echo "Error: No host detected; exiting";
                return;
            }
            if(getenv('alwaysssl') === 'true' && !$this->isSecure()) {
                $secureredirect = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                header("Location: $secureredirect");
                return;
            }
            if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                return;
            }
            if($router == null) {
                http_response_code(500);
                echo '500 Internal server error: Application router is not available.';
                return;
            }
            $router->readyDispatcher();
            $route = $router->trigger();
            if ($route->http_code === 404) {
                http_response_code(404);
                echo '404 Page not found.';
                return;
            }
            if ($route->http_code === 405) {
                http_response_code(405);
                echo '405 HTTP method not allowed.';
                return;
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
            try {
                if(is_null($methodname)) {
                    $methodname = 'index';
                }
                $action = new $actionname($request);
                $action->setup($methodname);
                if ($action->isExecutable()) {
                    $action->execute();
                }
            }
            catch (\TypeError $e) {
                error_log($e->getMessage()." at ".$e->getLine()." in ".$e->getFile());
                echo "Routing config failure or internal error; please check logs for more information";
                return;
            }
        }
    }

    private function isSecure() : bool {
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