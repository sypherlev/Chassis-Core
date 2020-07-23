<?php
/**
 * Basic Logging class for tossing stuff into a convenient location
 */

namespace SypherLev\Chassis;

use SypherLev\Chassis\Error\ChassisException;
use SypherLev\Chassis\Response\SlackResponse;

class Logger
{
    private $logtype;

    const TO_SLACK = 'slack';
    const TO_FILE = 'file';

    public function __construct(string $logtype = "")
    {
        if($logtype === "") {
            $this->logtype = self::TO_FILE;
        }
        else {
            $this->logtype = $logtype;
        }
    }

    /**
     * @psalm-suppress MissingParamType
     */
    public function log($thing) {
        if(is_string($thing) || is_int($thing) || is_float($thing)) {
            $this->storeString((string)$thing);
        }
        else if (is_bool($thing)) {
            if($thing) {
                $this->storeString('true');
            }
            else {
                $this->storeString('false');
            }
        }
        else if (is_null($thing)) {
            $this->storeString('NULL');
        }
        else if (is_resource($thing)) {
            $metadata = stream_get_meta_data($thing);
            $this->storeArray($metadata);
        }
        else if(is_array($thing)) {
            $this->storeArray($thing);
        }
        else if(is_a($thing, 'Exception')) {
            $this->storeException($thing);
        }
        else {
            // at this point, we're dealing with an object that possibly can't be stored,
            // so try to cast it to a string and hope for the best
            try {
                $thing = (string)$thing;
                $this->storeString($thing);
            }
            catch (\Exception $e) {
                throw new ChassisException("String cast failure: cannot log object of class ".get_class($thing));
            }
        }
    }

    public function setLogType(string $type) {
        $this->logtype = $type;
    }

    private function storeString(string $message) {
        $message = $this->createLogMessage($message);
        $this->logMessage($message, $this->logtype);
    }

    private function storeException(\Exception $e) {
        $message = $this->createLogMessage($e->getMessage().PHP_EOL.$e->getTraceAsString());
        $this->logMessage($message, $this->logtype);
    }

    private function storeArray(array $e) {
        $message = $this->createLogMessage(print_r($e, true));
        $this->logMessage($message, $this->logtype);
    }

    private function logMessage(string $message, string $type) {
        switch ($type) {
            case self::TO_SLACK:
                $this->logToSlack($message);
                break;
            default:
                $this->logToFile($message);
        }
    }

    private function logToFile(string $message) {
        $logfile = getenv('logfile');
        if($logfile !== false && strlen($logfile) > 0) {
            touch($logfile);
        }
        else {
            throw new ChassisException("Logfile location not found in .env; cannot log any output");
        }
        file_put_contents($logfile, $message, FILE_APPEND);
    }

    private function logToSlack(string $message) {
        $slackResponse = new SlackResponse();
        $slackResponse->setSlackParameters(getenv('slack_webhook'), getenv('slack_channel'), 'ChassisLogger',':interrobang:');
        $slackResponse->insertMessage($message);
        $slackResponse->out();
    }

    private function createLogMessage(string $message): string {
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return "IP: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
                $message.PHP_EOL.
                "-------------------------".PHP_EOL;
        }
        return 'CLI - '.date("F j, Y, g:i a").PHP_EOL.
            $message.PHP_EOL.
            "-------------------------".PHP_EOL;
    }
}