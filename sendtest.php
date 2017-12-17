<?php
$resque = require_once('resque.php');

//配置信息
$model = call_user_func($resque, array('vhost' => '/'));

echo '<pre>';
$message = array(
    "fanout MESSAGE! 广播消息！" . time(),
    "fanout MESSAGE! 广播消息！" . time(),
    "fanout MESSAGE! 广播消息！" . time(),
    "fanout MESSAGE! 广播消息！" . time(),
);
//需改变交换机或队列属性
$rep = $model->setExchange('test.direct', 'direct', AMQP_DURABLE)->send($message, 'direct_queue')->push();
print_r($rep);

$message = array(
    "fanout MESSAGE! 广播消息！" . time(),
    "fanout MESSAGE! 广播消息！" . time(),
    "fanout MESSAGE! 广播消息！" . time(),
    "fanout MESSAGE! 广播消息！" . time(),
);
$rep = $model->setExchange('exDemoFanout', 'fanout', AMQP_DURABLE)->send($message, 'demokey.fanout')->push();
print_r($rep);

$message = array(
    "topic MESSAGE! 匹配消息！" . time(),
    "topic MESSAGE! 匹配消息！" . time(),
    "topic MESSAGE! 匹配消息！" . time(),
    "topic MESSAGE! 匹配消息！" . time(),
);
$rep = $model->setExchange('exDemoTopic', 'topic', AMQP_DURABLE)->send($message, 'demo.key.topic')->push();
print_r($rep);

//rpc
for ($i=0;$i<10;$i++) {
    $model->setExchange()->rpc("rpc client MESSAGE! 测试消息！".time(), 'rpc_demokey', function($msg){
        var_dump($msg);
    })->push();
}
