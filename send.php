<?php
include(__DIR__ . '/config.php');
include(__DIR__ . '/rabbit.php');

// usage: send.php [count]
// e.g. php -f send.php 100

$count = isset($argv[1]) ? $argv[1] : 1;

$exchange = "test";
$queues   = array("test-queue");
$host     = HOST;
$port     = PORT;
$user     = USER;
$pass     = PASS;
$vhost    = VHOST;

$rabbit = new Rabbit($host, $port, $user, $pass, $exchange, $queues, $vhost);

$msg_body = "hello";

$routing_key = $queues[0];

for ($i = 1; $i <= $count; $i++) {
    $rabbit->send($msg_body . " $i of $count", $routing_key);
}

$rabbit->close();
