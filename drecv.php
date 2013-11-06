<?php // drecv.php
require_once __DIR__.'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$host  = 'localhost';
$port  = 5672;
$vhost = '/';
$user  = 'guest';
$pass  = 'guest';
$xchg  = 'amq.direct';
$queue = 'events';

$conn  = new AMQPConnection($host, $port, $user, $pass, $vhost);
$ch    = $conn->channel();

$msg = $ch->basic_consume();

print_r($msg);
