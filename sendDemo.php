<?php
$resque = require_once('resque.php');

//配置信息
$model = call_user_func($resque, array(
//    'host'     => 'localhost',
//    'port'     => '5672',
//    'login'    => 'yaofang',
//    'password' => 'yaofang',
//    'vhost'    => 'demo.cn',//虚拟机
));

$message = array(
    "TEST MESSAGE! 测试消息！" . time(),
    "TEST MESSAGE! 测试消息！" . time(),
    "TEST MESSAGE! 测试消息！" . time(),
    "TEST MESSAGE! 测试消息！" . time(),
);
echo '<pre>';
//方法一：
//需改变交换机或队列属性
$rep = $model->setExchange('exDemoDirect', 'direct', AMQP_DURABLE | AMQP_AUTODELETE)->send($message, 'demokey')->push();
print_r($rep);

$message = array(
    "fanout MESSAGE! 广播消息！" . time(),
    "fanout MESSAGE! 广播消息！" . time(),
    "fanout MESSAGE! 广播消息！" . time(),
    "fanout MESSAGE! 广播消息！" . time(),
);
$rep = $model->setExchange('exDemoFanout', 'fanout', AMQP_DURABLE | AMQP_AUTODELETE)->send($message, 'demokey.fanout')->push();
print_r($rep);

$message = array(
    "topic MESSAGE! 匹配消息！" . time(),
    "topic MESSAGE! 匹配消息！" . time(),
    "topic MESSAGE! 匹配消息！" . time(),
    "topic MESSAGE! 匹配消息！" . time(),
);
$rep = $model->setExchange('exDemoTopic', 'topic', AMQP_DURABLE | AMQP_AUTODELETE)->send($message, 'demo.key.topic')->push();
print_r($rep);