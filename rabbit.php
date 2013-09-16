<?php
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Rabbit {

    private $connection;
    private $channel;
    private $exchange;
    private $queue;

    public function __construct($host, $port, $user, $pass, $exchange, $queue) {

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

        $ch->queue_bind($queue, $exchange);

        $this->connection = $conn;
        $this->channel    = $ch;
        $this->exchange   = $exchange;
        $this->queue      = $queue;
    }

    public function send($body) {
        $msg = new AMQPMessage(
            $body, 
            array('content_type'  => 'text/plain', 
                  'delivery_mode' => 2));
        $this->channel->basic_publish($msg, $this->exchange);
    }

    public function listen($callback) {
        $this->channel->basic_consume(
            $this->queue, // name
            'recv.php',   // consumer_tag
            false,        // no_local?
            false,        // no_ack?
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
