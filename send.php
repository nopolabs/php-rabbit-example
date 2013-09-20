<?php
include(__DIR__ . '/config.php');
include(__DIR__ . '/rabbit.php');

// usage: send.php [count]
// e.g. php -f send.php 100

$count = isset($argv[1]) ? $argv[1] : 1;

$exchange    = "test";
$queue       = "test-queue";

$rabbit = new Rabbit(HOST, PORT, USER, PASS, $exchange, array($queue));

$msg_body = "hello";

$routing_key = $queue;

for ($i = 1; $i <= $count; $i++) {
    $rabbit->send($msg_body . " $i of $count", $routing_key);
}

$rabbit->close();
