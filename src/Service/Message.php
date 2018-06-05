<?php

namespace SypherLev\Chassis\Service;

class Message
{
    private $is_normal = true;
    private $error;
    private $data;
    private $status;

    public function setData($data) {
        $this->data = $data;
    }

    public function getData() {
        return $this->data;
    }

    public function isNormal() : bool {
        return $this->is_normal;
    }

    public function hasProblem() {
        $this->is_normal = false;
    }

    public function storeException(\Exception $e) {
        $this->error = $e;
    }

    public function getException() : \Exception {
        return $this->error;
    }

    public function getStatus() : string {
        return $this->status;
    }

    public function setStatus(string $status) {
        $this->status = $status;
    }
}