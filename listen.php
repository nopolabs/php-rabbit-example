<?php
include(__DIR__ . '/config.php');
include(__DIR__ . '/rabbit.php');

$rabbit = new Rabbit(HOST, PORT, USER, PASS, EXCHANGE, QUEUE);

$callback = function($msg) {
    echo " [x] Received ", $msg->body, "\n";

    $info = $msg->delivery_info;

    $ch = $info['channel'];
    
    $ch->basic_ack($info['delivery_tag']);
    
    if ($msg->body === 'quit') {
        $ch->basic_cancel($info['consumer_tag']);
    }
};

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$rabbit->listen($callback);

$rabbit->close();
