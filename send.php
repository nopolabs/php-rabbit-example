<?php
include(__DIR__ . '/config.php');
include(__DIR__ . '/rabbit.php');

$ack = isset($argv[1]) ? $argv[1] : "auto";
$count = isset($argv[2]) ? $argv[2] : 1;

$exchange    = "test";
$routing_key = $ack . "Ack";
$queue       = $routing_key;

$rabbit = new Rabbit(HOST, PORT, USER, PASS, $exchange, $queue);

$msg_body = "hello " . $ack;

for ($i = 1; $i <= $count; $i++) {
    $rabbit->send($msg_body . " $i of $count", $routing_key);
}

$rabbit->close();
