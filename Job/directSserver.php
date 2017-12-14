<?php
//任务队列消费端
//require_once(dirname(dirname(__DIR__)).'/component.yaofang.cn/demo/cy_demo.php');
$m = require_once('../resque.php');
$c = call_user_func($m, array(
//    'host'     => '192.168.10.185',
//    'port'     => '5672',
//    'login'    => 'yaofang',
//    'password' => 'yaofang',
//    'vhost'    => 'demo.cn',//虚拟机
));

$c->setExchange('test.direct', 'direct', AMQP_DURABLE | AMQP_AUTODELETE)->addQueue('direct_queue', 'direct_queue', AMQP_DURABLE | AMQP_AUTODELETE)
    ->consume(function ($envelope, $queue) {

    //取出消息
    $msg = $envelope->getBody();

    //todo your logic...
    //    echo json_encode(call_user_func_array(array(new cy_demo,'get'), array('test0001', 1)));
    echo mb_convert_encoding($msg, 'GBK', 'UTF-8'); //处理消息

    //如果交换机类型是RPC, 需手动发送ACK应答
    echo '---'.$index = $envelope->getDeliveryTag();
    $queue->ack($index);

    echo "\n";
})->pull();