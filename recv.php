<?php
include(__DIR__ . '/config.php');
include(__DIR__ . '/rabbit.php');

// usage: recv.php
// e.g. php -f recv.php

// "auto" or "basic" selects whether callback needs
// to send basic_ack to acknowledge message receipt.
$ack = "basic"; 
$auto_ack = $ack == "auto";

$exchange     = "test-exchange";
$queue        = "test-queue";
$routing_key  = $queue;
$host         = HOST;
$port         = PORT;
$user         = USER;
$pass         = PASS;
$vhost        = VHOST;
$consumer_tag = "recv.php";

$rabbit = new Rabbit($host, $port, $user, $pass, $exchange, $vhost);

$callback = function($msg) use ($auto_ack) {

    $info = $msg->delivery_info;
    $tag  = $info["consumer_tag"];
    $ch   = $info['channel'];

    echo " [$tag] Received ", $msg->body, "\n";
    
    if (!$auto_ack) {
        $ch->basic_ack($info['delivery_tag']);
    }
    
    if ($msg->body === 'quit') {
        $ch->basic_cancel($tag);
    }
};

echo "[$consumer_tag] Waiting for messages. To exit press CTRL+C\n";

$rabbit->listen($queue, $callback, $consumer_tag, $auto_ack);

$rabbit->close();
