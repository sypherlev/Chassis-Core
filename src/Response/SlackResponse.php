<?php

namespace SypherLev\Chassis\Response;

use SypherLev\Chassis\Error\ChassisException;

class SlackResponse implements ResponseInterface
{
    private $data = [];
    private $message = "";
    private $url;
    private $channel;
    private $user;
    private $emoji = ":interrobang:";

    /**
     * @psalm-suppress MissingParamType
     */
    public function insertOutputData(string $label, $data)
    {
        $this->data[$label] = $data;
    }

    public function insertMessage(string $message) {
        $this->message = $message;
    }

    public function setSlackParameters(string $webhook, string $channel, string $user, string $emoji = 'interrobang')
    {
        if(empty($channel) || empty($webhook)) {
            throw new ChassisException('Cannot log to Slack; channel or webhook setting is missing');
        }
        $this->url = $webhook;
        $this->channel = $channel;
        $this->user = $user;
        $this->emoji = $emoji;
    }

    public function out()
    {
        $message = "===========".PHP_EOL;
        if($this->message != "") {
            $message .= $this->message.PHP_EOL;
            $message .= "===========".PHP_EOL;
        }
        foreach ($this->data as $idx => $data) {
            if(is_array($data)) {
                $message .= print_r($data, true).PHP_EOL;
            }
            else {
                $message .= (string) $data.PHP_EOL;
            }
            $message .= "----------";
        }

        $data = array(
            'channel'     => $this->channel,
            'username'    => $this->user,
            'text'        => $message,
            'icon_emoji'  => $this->emoji
        );

        $data_string = json_encode($data);

        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        $result = curl_exec($ch);
        if($result === false) {
            // transfer failure
            $error = curl_error($ch);
            throw new ChassisException('Curl request to Slack has failed with error: '.$error);
        }
    }
}