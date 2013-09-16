<?php
include(__DIR__ . '/config.php');
include(__DIR__ . '/rabbit.php');
        
$rabbit = new Rabbit(HOST, PORT, USER, PASS, EXCHANGE, QUEUE);

$msg_body = isset($argv[1]) ? $argv[1] : "hello AMQP";

$rabbit->send($msg_body);

$rabbit->close();
