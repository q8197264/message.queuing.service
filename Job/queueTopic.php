<?php
$m = require_once('../resque.php');
$c = call_user_func($m, array(
    //    'host'     => '192.168.10.185',
    //    'port'     => '5672',
    //    'login'    => 'yaofang',
    //    'password' => 'yaofang',
    //    'vhost'    => 'demo.cn',//虚拟机
));

$c->setExchange('exDemoTopic', 'topic', AMQP_DURABLE | AMQP_AUTODELETE)->addQueue('queueTopic', '#.key.#', AMQP_DURABLE | AMQP_AUTODELETE)
    ->consume(function ($envelope, $queue) {

        //取出消息
        $msg = $envelope->getBody();

        //todo your logic...
        echo mb_convert_encoding($msg, 'GBK', 'UTF-8'); //处理消息


        //如果交换机类型是RPC, 需手动发送ACK应答
        echo '---'.$index = $envelope->getDeliveryTag();
        $queue->nack($index);

        echo "\n";
    })->pull();