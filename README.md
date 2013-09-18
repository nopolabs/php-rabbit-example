# Example code for testing and understanding interactions between PHP and RabbitMQ

## Uses composer to load dependencies. 
See http://getcomposer.org

## send.php
    usage: send.php [auto|basic] [count]
  
    e.g. php -f send.php auto 100

    auto and basic are used to selected a queue and a routingKey
    with either autoAck or basicAck behavior. Actually implementing
    those behaviors is the responsibility of the queue consumer,
    see recv.php

## recv.php
    usage: recv.php [auto|basic]
    
    e.g. php -f recv.php auto
