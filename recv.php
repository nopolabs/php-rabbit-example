<?php
include(__DIR__ . '/config.php');
include(__DIR__ . '/rabbit.php');

// usage: recv.php
// e.g. php -f recv.php

// "auto" or "basic" selects whether callback needs
// to send basic_ack to ackknowledge message receipt.
$ack = "basic"; 
$auto_ack = $ack == "auto";

$exchange = "test";
$queue    = "test-queue";

$rabbit = new Rabbit(HOST, PORT, USER, PASS, $exchange, array($queue));

$callback = function($msg) use ($auto_ack) {

    echo " [x] Received ", $msg->body, "\n";

    $info = $msg->delivery_info;

    $ch = $info['channel'];
    
    if (!$auto_ack) {
        $ch->basic_ack($info['delivery_tag']);
    }
    
    if ($msg->body === 'quit') {
        $ch->basic_cancel($info['consumer_tag']);
    }
};

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$rabbit->listen($queue, $callback, $auto_ack);

$rabbit->close();
