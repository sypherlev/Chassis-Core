<?php
/**
 * Basic Logging class for tossing stuff into a convenient file
 */

namespace SypherLev\Chassis;

class Logger
{
    public static function store($message) {
        $message = self::createLogMessage($message);
        self::logToFile($message);
    }

    public static function logToFile($message) {
        $logfile = getenv('logfile');
        if(!is_null($logfile) && $logfile !== "" && $logfile !== false) {
            touch($logfile);
        }
        else {
            $logfile = "../../chassis.log";
            touch($logfile);
        }
        file_put_contents($logfile, $message, FILE_APPEND);
    }

    public static function createLogMessage($message) {
        return "IP: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
            $message.PHP_EOL.
            "-------------------------".PHP_EOL;
    }
}