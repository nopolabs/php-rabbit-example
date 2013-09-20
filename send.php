<?php
include(__DIR__ . '/config.php');
include(__DIR__ . '/rabbit.php');

// usage: send.php [auto|basic] [count]
// e.g. php -f send.php auto 100

// auto and basic are used to selected a queue and a routingKey
// with either autoAck or basicAck behavior. Actually implementing
// those behaviors is the responsibility of the queue consumer,
// see recv.php

$ack = isset($argv[1]) ? $argv[1] : "basic";
$count = isset($argv[2]) ? $argv[2] : 1;

$exchange    = "test";
$routing_key = $ack . "Ack";
$queue       = $routing_key;

$rabbit = new Rabbit(HOST, PORT, USER, PASS, $exchange, $queue, $routing_key);

$msg_body = "hello " . $ack;

for ($i = 1; $i <= $count; $i++) {
    $rabbit->send($msg_body . " $i of $count");
}

$rabbit->close();
