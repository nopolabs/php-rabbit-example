<?php
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Rabbit {

    private $connection;
    private $channel;
    private $exchange;
    private $exchangeType;
    private $routing;

    public function __construct($amqp) 
    {
        $this->connection   = $amqp;       
        $this->channel      = $this->connection->channel();
        $this->exchange     = '';
        $this->exchangeType = 'direct';
        $this->routing      = array();
    }

    public function useExchange($exchange) 
    {
        $this->exchange = $exchange;
    }

    public function declareExchange($exchange, 
                                    $exchangeType = 'direct', 
                                    $passive      = false, 
                                    $durable      = true, 
                                    $autoDelete   = false) 
    {
        $this->exchange     = $exchange;
        $this->exchangeType = $exchangeType;
        $this->channel->exchange_declare($exchange, $exchangeType, $passive, $durable, $autoDelete);
    }

    public function declareQueue($queue,
                                 $passive    = false, 
                                 $durable    = true, 
                                 $exclusive  = true, 
                                 $autoDelete = false) 
    {
        $this->channel->queue_declare($queue, $passive, $durable, $exchange, $autoDelete);
    }

    public function send($queue, $body, $properties = null) 
    {
        $msg = new AMQPMessage($body, $properties);
        $this->channel->basic_publish($msg, $this->exchange, $this->get_routing_key($queue));
    }

    public function listen($queue, $callback, $auto_ack = true, $consumer_tag = 'Rabbit::listen') 
    {
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

    public function close() 
    {
        $this->channel->close();
        $this->connection->close();
    }

    // lazily declare and bind queues using a direct mapping: routing_key -> queue
    protected function get_routing_key($queue) 
    {
        if (!isset($this->routing[$queue])) {
            //                            name,   passive?,durable?,exclusive?,auto_delete?
            $this->channel->queue_declare($queue, false,   true,    false,     false);

            $this->routing[$queue]= $queue;

            $this->channel->queue_bind($queue, $this->exchange, $this->routing[$queue]);
        }

        return $this->routing[$queue];
    }
    
}
