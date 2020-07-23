<?php

namespace SypherLev\Chassis\Response;

interface ResponseInterface {

    /**
     * @psalm-suppress MissingParamType
     */
    public function insertOutputData(string $label, $data);
    public function out();
}