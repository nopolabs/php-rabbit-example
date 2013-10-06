<?php
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Rabbit {

    private $connection;
    private $channel;
    private $exchange;
    private $routing;

    public function __construct($host, $port, $user, $pass, $exchange, $vhost = "/") {

        $this->connection = new AMQPConnection($host, $port, $user, $pass, $vhost);
        
        $this->channel = $this->connection->channel();
        //                               name,      type,     passive?,durable?,auto_delete?
        $this->channel->exchange_declare($exchange, 'direct', false,   true,    false);

        $this->exchange = $exchange;
        $this->routing  = array();
    }

    public function send($body, $queue, $persistent = true) {

        echo "sending: $body\n";
        $msg = new AMQPMessage(
            $body, 
            array('content_type'  => 'text/plain', 
                  'delivery_mode' => $persistent ? 2 : 1));
        $this->channel->basic_publish($msg, $this->exchange, $this->get_routing_key($queue));
    }

    public function listen($queue, $callback, $consumer_tag = 'Rabbit::listen', $auto_ack = true) {

        $this->channel->basic_consume(
            $queue,        // name
            $consumer_tag, // consumer_tag
            false,         // no_local?
            $auto_ack,     // no_ack?
            false,         // exclusive?
            false,         // nowait?
            $callback);    // callback

        while(count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    public function close() {
        $this->channel->close();
        $this->connection->close();
    }

    // lazily declare and bind queues using a direct mapping: routing_key -> queue
    protected function get_routing_key($queue) {

        if (!isset($this->routing[$queue])) {
            //                            name,   passive?,durable?,exclusive?,auto_delete?
            $this->channel->queue_declare($queue, false,   true,    false,     false);

            $this->routing[$queue]= $queue;

            $this->channel->queue_bind($queue, $this->exchange, $this->routing[$queue]);
        }

        return $this->routing[$queue];
    }
    
}
