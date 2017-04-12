<?php

namespace SypherLev\Chassis\Middleware;

class Collection
{
    private $queues = [];

    public function run($queueName, $input) {
        if(isset($this->queues[$queueName])) {
            $newQueue = clone $this->queues[$queueName];
            return $newQueue->runQueue($input);
        }
        return $input;
    }

    public function loadQueue($label, $process) {
        $this->queues[$label] = $process;
    }
}