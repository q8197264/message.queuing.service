<?php
/**
 * RPC客户端
 */
$routing_key = 'rpc_queue';
$num = empty($_GET['n']) ? mt_rand(6,100) : intval($_GET['n']);

// 建立TCP连接
$connection = new AMQPConnection([
                                     'host' => '192.168.10.185',
                                     'port' => '5672',
                                     'vhost' => '/',
                                     'login' => 'admin',
                                     'password' => 'admin'
                                 ]);
$connection->connect() or die("Cannot connect to the broker!\n");

$channel = new AMQPChannel($connection);

$client_queue = new AMQPQueue($channel);
$client_queue->setFlags(AMQP_EXCLUSIVE);
$client_queue->declareQueue();
$callback_queue_name = $client_queue->getName();

$corr_id = uniqid();
$properties = [
    'correlation_id' => $corr_id,
    'reply_to' => $callback_queue_name
];

$exchange = new AMQPExchange($channel);
$exchange->publish($num, $routing_key, AMQP_NOPARAM, $properties);

$client_queue->consume(function($envelope, $queue) use ($corr_id){
    if ($envelope->getCorrelationId() == $corr_id) {
        $msg = $envelope->getBody();
        var_dump('Received Data: ' . $msg);
        $queue->nack($envelope->getDeliveryTag());
        return false;
    }
});

// 断开连接
$connection->disconnect();