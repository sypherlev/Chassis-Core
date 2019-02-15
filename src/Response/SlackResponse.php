<?php

namespace SypherLev\Chassis\Response;

class SlackResponse implements ResponseInterface
{
    private $data = [];
    private $url;
    private $channel;
    private $user;
    private $emoji;

    public function insertOutputData($label, $data)
    {
        $this->data[$label] = $data;
    }

    public function setSlackParameters($url, $channel, $user, $emoji = 'interrobang') {
        $this->url = $url;
        $this->channel = $channel;
        $this->user = $user;
        $this->emoji = $emoji;
    }

    public function out()
    {
        try {
            $data = "payload=" . json_encode(array(
                    "channel" => "#" . $this->channel,
                    "text" => '```' . print_r($this->data, true) . '```',
                    "icon_emoji" => ':' . $this->emoji . ':',
                    "user" => $this->user
                ));

            // You can get your webhook endpoint from your Slack settings
            $ch = curl_init($this->url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $responsecode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($responsecode != 200) {
                error_log("Slack error: " . curl_error($ch) . " | " . json_encode($result) . " | " . $responsecode . "\n");
            }
            curl_close($ch);
        }
        catch (\Exception $e) {
            error_log($e->getMessage()." at line ".$e->getLine()." in ".$e->getFile());
        }
    }
}