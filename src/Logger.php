<?php
/**
 * Basic Logging class for tossing stuff into a convenient file
 */

namespace SypherLev\Chassis;

class Logger
{
    private $logtype;

    const TO_SLACK = 'slack';
    const TO_FILE = 'file';

    public function __construct($logtype = null)
    {
        if(is_null($logtype)) {
            $this->logtype = self::TO_FILE;
        }
        else {
            $this->logtype = $logtype;
        }
    }

    public function log($thing) {
        if(is_string($thing)) {
            $this->storeString($thing);
            return;
        }
        if(is_array($thing)) {
            $this->storeArray($thing);
            return;
        }
        if(is_a($thing, 'Exception')) {
            $this->storeException($thing);
            return;
        }
        // at this point, we're dealing with a thing that possibly can't be stored,
        // so cast it to a string and hope for the best
        $thing = (string)$thing;
        $this->storeString($thing);
    }

    public function setLogType($type) {
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

    private function logMessage($message, $type) {
        switch ($type) {
            case self::TO_SLACK:
                $this->logToSlack($message);
                break;
            case self::TO_FILE:
                $this->logToFile($message);
                break;
            default:
                $this->logToFile($message);
        }
    }

    private function logToFile($message) {
        $logfile = getenv('logfile');
        if(!is_null($logfile) && $logfile !== "" && $logfile !== false) {
            touch($logfile);
        }
        else {
            $logfile = "../chassis.log";
            touch($logfile);
        }
        file_put_contents($logfile, $message, FILE_APPEND);
    }

    private function logToSlack($message) {

        $channel  = getenv('slack_channel');
        $bot_name = 'Webhook';
        $icon     = ':interrobang:';

        $data = array(
            'channel'     => $channel,
            'username'    => $bot_name,
            'text'        => $message,
            'icon_emoji'  => $icon
        );

        $data_string = json_encode($data);

        $ch = curl_init(getenv('slack_webhook'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        curl_exec($ch);
    }

    private function createLogMessage($message) {
        if (php_sapi_name() == "cli") {
            return 'CLI - '.date("F j, Y, g:i a").PHP_EOL.
                $message.PHP_EOL.
                "-------------------------".PHP_EOL;
        }
        return "IP: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
            $message.PHP_EOL.
            "-------------------------".PHP_EOL;
    }
}