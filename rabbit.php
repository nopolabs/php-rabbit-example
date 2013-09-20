<?php
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Rabbit {

    private $connection;
    private $channel;
    private $exchange;

    public function __construct($host, $port, $user, $pass, $exchange, $queues) {

        $conn = new AMQPConnection($host, $port, $user, $pass);
        
        $ch = $conn->channel();

        $ch->exchange_declare(
            $exchange, // name
            'direct',  // type
            false,     // passive?
            true,      // durable?
            false);    // auto_delete?

        foreach ($queues as $queue) {

            $ch->queue_declare(
                $queue,    // name
                false,     // passive?
                true,      // durable?
                false,     // exclusive?
                false);    // auto_delete?

            $routing_key = $queue;

            $ch->queue_bind($queue, $exchange, $routing_key);
        }

        $this->connection  = $conn;
        $this->channel     = $ch;
        $this->exchange    = $exchange;
    }

    public function send($body, $routing_key) {
        $msg = new AMQPMessage(
            $body, 
            array('content_type'  => 'text/plain', 
                  'delivery_mode' => 2)); // persistent
        $this->channel->basic_publish($msg, $this->exchange, $routing_key);
    }

    public function listen($queue, $callback, $auto_ack = true) {
        $this->channel->basic_consume(
            $queue, // name
            'recv.php',   // consumer_tag
            false,        // no_local?
            $auto_ack,    // no_ack?
            false,        // exclusive?
            false,        // nowait?
            $callback);   // callback

        while(count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    public function close() {
        $this->channel->close();
        $this->connection->close();
    }
    
}
