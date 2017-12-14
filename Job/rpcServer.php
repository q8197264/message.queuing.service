<?php
//任务队列消费端
$m = require_once('../resque.php');
$c = call_user_func($m, array(
        'host'     => '127.0.0.1',
        'port'     => '5672',
        'login'    => 'yaofang',
        'password' => 'yaofang',
        'vhost'    => '/',//虚拟机
));

$c->setExchange()->addQueue('rpc_demokey', null, null)->consume(function ($envelope, $queue, $exchange) {

        //取出消息
        $msg = $envelope->getBody();

        //todo your logic...
        echo mb_convert_encoding($msg, 'GBK', 'UTF-8'); //处理消息
        $exchange->publish($msg, $envelope->getReplyTo(), AMQP_NOPARAM, array(
            'correlation_id' => $envelope->getCorrelationId(),
        ));

        //如果交换机类型是RPC, 需手动发送ACK应答
        echo '---'.$index = $envelope->getDeliveryTag();
        $queue->ack($index);

        echo "\n";
    })->pull();