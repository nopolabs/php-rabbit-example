<?php
include(__DIR__ . '/config.php');
include(__DIR__ . '/rabbit.php');

// usage: send.php [count]
// e.g. php -f send.php 100

$count = isset($argv[1]) ? $argv[1] : 1;

$exchange    = "devo";
$queue       = "test-queue";
$routing_key = $queue;
$host        = HOST;
$port        = PORT;
$user        = USER;
$pass        = PASS;
$vhost       = VHOST;

$rabbit = new Rabbit($host, $port, $user, $pass, $exchange, $vhost);

$msg_body = "Fanmail from a flounder.";

$properties = array('content_type'  => 'text/plain',
                    'delivery_mode' => 1); // 1 == not persistent, 2 == persistent

for ($i = 1; $i <= $count; $i++) {
    $rabbit->send($routing_key, $msg_body . " $i of $count", $properties);
}

$rabbit->close();
