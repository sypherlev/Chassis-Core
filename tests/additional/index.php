<?php

require "global_function_overrides.php";
require __DIR__ . '/../../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__.'/../../');
$dotenv->load();

$chassis = new SypherLev\Chassis\Ignition();
$chassis->run(
    new \Tests\additional\RouteCollection(), false
);