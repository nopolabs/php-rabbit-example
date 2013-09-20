<?php
include(__DIR__ . '/config.php');
include(__DIR__ . '/rabbit.php');

// usage: recv.php [auto|basic]
// e.g. php -f recv.php auto

$ack = isset($argv[1]) ? $argv[1] : "basic";
$auto_ack = $ack == "auto"

$exchange    = "test";
$routing_key = $ack . "Ack";
$queue       = $routing_key;

$rabbit = new Rabbit(HOST, PORT, USER, PASS, $exchange, $queue, $routing_key);

$callback = function($msg) {
    echo " [x] Received ", $msg->body, "\n";

    $info = $msg->delivery_info;

    $ch = $info['channel'];
    
    if (!auto_ack) {
        $ch->basic_ack($info['delivery_tag']);
    }
    
    if ($msg->body === 'quit') {
        $ch->basic_cancel($info['consumer_tag']);
    }
};

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$rabbit->listen($callback, $auto_ack);

$rabbit->close();
