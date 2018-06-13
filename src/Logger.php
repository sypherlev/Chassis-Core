<?php
/**
 * Basic Logging class for tossing stuff into a convenient file
 */

namespace SypherLev\Chassis;

class Logger
{
    public static function store(string $message, $destination = 'file') {
        $message = self::createLogMessage($message);
        if($destination == 'slack') {
            self::logToSlack($message);
            return;
        }
        self::logToFile($message);
    }

    public static function storeException(\Exception $e, $destination = 'file') {
        $message = self::createLogMessage($e->getMessage().PHP_EOL.$e->getTraceAsString());
        if($destination == 'slack') {
            self::logToSlack($message);
            return;
        }
        self::logToFile($message);
    }

    public function storeArray(array $e, $destination = 'file') {
        $message = self::createLogMessage(print_r($e, true));
        if($destination == 'slack') {
            self::logToSlack($message);
            return;
        }
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

    public static function logToSlack($message) {

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

    public static function createLogMessage($message) {
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