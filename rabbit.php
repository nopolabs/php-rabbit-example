<?php
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Rabbit {

    private $connection;
    private $channel;
    private $exchange;
    private $queue;
    private $routing_key;

    public function __construct($host, $port, $user, $pass, $exchange, $queue, $routing_key) {

        $conn = new AMQPConnection($host, $port, $user, $pass);
        
        $ch = $conn->channel();

        $ch->queue_declare(
            $queue,    // name
            false,     // passive?
            true,      // durable?
            false,     // exclusive?
            false);    // auto_delete?

        $ch->exchange_declare(
            $exchange, // name
            'direct',  // type
            false,     // passive?
            true,      // durable?
            false);    // auto_delete?

        $ch->queue_bind($queue, $exchange, $routing_key);

        $this->connection = $conn;
        $this->channel    = $ch;
        $this->exchange   = $exchange;
        $this->queue      = $queue;
        $this->routing_key      = $routing_key;
    }

    public function send($body) {
        $msg = new AMQPMessage(
            $body, 
            array('content_type'  => 'text/plain', 
                  'delivery_mode' => 2)); // persistent
        $this->channel->basic_publish($msg, $this->exchange, $this->routing_key);
    }

    public function listen($callback, $auto_ack = true) {
        $this->channel->basic_consume(
            $this->queue, // name
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
