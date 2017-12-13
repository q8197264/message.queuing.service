<?php
/**
 * RPC服务端
 */
register_shutdown_function(function(){
    echo PHP_EOL.'close'.PHP_EOL;
});

/* linux */
//declare(ticks=1);
////信号处理函数
//function sig_handler($signo)
//{
//    switch ($signo) {
//        case SIGTERM:
//            // 处理kill
//            echo PHP_EOL.'kill';
//            exit;
//            break;
//        case SIGHUP:
//            //处理SIGHUP信号
//            break;
//        case SIGINT:
//            //处理ctrl+c
//            echo PHP_EOL.'ctrl+c';
//            exit;
//            break;
//        default:
//            // 处理所有其他信号
//    }
//}
//
////安装信号处理器
//pcntl_signal(SIGTERM, "sig_handler");
//pcntl_signal(SIGHUP,  "sig_handler");
//pcntl_signal(SIGINT,  "sig_handler");

$routing_key = 'rpc_queue';

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
$channel->setPrefetchCount(1);

$server_queue = new AMQPQueue($channel);
$server_queue->setName($routing_key);
$server_queue->declareQueue();

$exchange = new AMQPExchange($channel);

$server_queue->consume(function($envelope, $queue) use ($exchange){
    $num = intval($envelope->getBody());
    $response = fib($num);
    $exchange->publish($response, $envelope->getReplyTo(), AMQP_NOPARAM, [
        'correlation_id' => $envelope->getCorrelationId(),
    ]);
    $queue->ack($envelope->getDeliveryTag());
});

// 断开连接
$connection->disconnect();

// 斐波那契函数
function fib($num) {
    if ($num == 0)
        return 1;
    else if ($num == 1)
        return 1;
    else
        return fib($num - 1) + fib($num - 2);
}