<?php

namespace SypherLev\Chassis\Response;


class ApiResponse implements ResponseInterface
{
    private $data = [];
    private $httpcode = 404;
    private $message = 'No response found';

    public function setHTTPCode($code)
    {
        $this->httpcode = (int)$code;
    }

    public function setOutputMessage($message)
    {
        $this->message = $message;
    }

    public function insertOutputData($label, $data)
    {
        $this->data[$label] = $data;
    }

    public function out()
    {
        http_response_code($this->httpcode);
        header("Content-type:application/json");
        echo json_encode(array('message' => $this->message, 'data' => $this->data), JSON_NUMERIC_CHECK);
    }

    public function dataResponse($label, $variable)
    {
        // check if there is no data but the response is still valid
        if (is_array($variable) && count($variable) == 0) {
            $this->setHTTPCode(200);
            $this->setOutputMessage('No data');
            $this->insertOutputData($label, $variable);
            $this->out();
            return;
        }
        if (!empty($variable)) {
            $this->setHTTPCode(200);
            $this->setOutputMessage('Data retrieved');
            $this->insertOutputData($label, $variable);
        } else {
            $this->setHTTPCode(500);
            $this->setOutputMessage('Data not found');
        }
        $this->out();
    }

    public function messageResponse($message, $isOkay = true)
    {
        if ($isOkay) {
            $this->setHTTPCode(200);
        } else {
            $this->setHTTPCode(500);
        }
        $this->setOutputMessage($message);
        $this->out();
    }
}