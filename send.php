<?php
include(__DIR__ . '/config.php');
include(__DIR__ . '/rabbit.php');

// usage: send.php [count]
// e.g. php -f send.php 100

$count = isset($argv[1]) ? $argv[1] : 1;

$uri = isset($argv[2]) ? $argv[2] : HOST . ":" . PORT . "/" . VHOST . "/devo/test-queue"; 

preg_match('#([^:/]+):(\d+)/(/|[^/]+)/([^/]+)/([^/]+)#', $uri, $matches);

echo "$uri\n";

$host        = $matches[1];
$port        = $matches[2];
$vhost       = $matches[3];
$exchange    = $matches[4];
$queue       = $matches[5];
$user        = USER;
$pass        = PASS;
$routing_key = $queue;

$rabbit = new Rabbit($host, $port, $user, $pass, $exchange, $vhost);

$msg_body = "Fanmail from a flounder.";

$properties = array('content_type'  => 'text/plain',
                    'delivery_mode' => 1); // 1 == not persistent, 2 == persistent

for ($i = 1; $i <= $count; $i++) {
    $rabbit->send($routing_key, $msg_body . " $i of $count", $properties);
}

$rabbit->close();
