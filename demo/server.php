<?php
/**
 * RPC服务端
 */


/* linux */
//register_shutdown_function(function(){
//    echo PHP_EOL.'close'.PHP_EOL;
//});
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

// 建立TCP连接
$connection = new AMQPConnection([
                                     'host' => 'localhost',
                                     'port' => '5672',
                                     'vhost' => '/',
                                     'login' => 'admin',
                                     'password' => 'admin'
                                 ]);
$connection->connect() or die("Cannot connect to the broker!\n");

$channel = new AMQPChannel($connection);
//多消费端时，当某个消息比较耗时处理中时，不再接收下一条消息，直到处理结束再继续接收,这段时间内的消息发往其它空闲的消费者
$channel->setPrefetchCount(1);

$routing_key = 'rpc_queue';//routing key必须与rpc队列同名
$rpc_queue = new AMQPQueue($channel);
$rpc_queue->setName($routing_key);
$rpc_queue->declareQueue();

$exchange = new AMQPExchange($channel);//可选
$rpc_queue->consume(function($envelope, $queue) use ($exchange){
    $num = intval($envelope->getBody());
    $response = fib($num);
    $exchange->publish($response, $envelope->getReplyTo(), AMQP_NOPARAM, [
        'correlation_id' => $envelope->getCorrelationId(),
    ]);

    echo $index = $envelope->getDeliveryTag();
    $queue->ack($index);
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