<?php
/*
//linux
register_shutdown_function(function(){
    echo PHP_EOL.'close'.PHP_EOL;
});
declare(ticks=1);
//信号处理函数
function sig_handler($signo)
{
    switch ($signo) {
        case SIGTERM:
            // 处理kill
            echo PHP_EOL.'kill';
            exit;
            break;
        case SIGHUP:
            //处理SIGHUP信号
            break;
        case SIGINT:
            //处理ctrl+c
            echo PHP_EOL.'ctrl+c';
            exit;
            break;
        default:
            // 处理所有其他信号
    }
}

//安装信号处理器
pcntl_signal(SIGTERM, "sig_handler");
pcntl_signal(SIGHUP,  "sig_handler");
pcntl_signal(SIGINT,  "sig_handler");
*/
//任务队列消费端
$m = require_once('../resque.php');
$c = call_user_func($m, array(
//        'host'     => '127.0.0.1',
//        'port'     => '5672',
//        'login'    => 'yaofang',
//        'password' => 'yaofang',
//        'vhost'    => '/',//虚拟机
));

$c->setExchange()->addQueue('rpc_demokey', null, AMQP_AUTODELETE)->consume(function ($envelope, $queue, $exchange) {

        //取出消息
        $msg = $envelope->getBody();

        //todo your logic...
        echo "\n".mb_convert_encoding($msg, 'GBK', 'UTF-8')."\n"; //处理消息

        $exchange->publish($msg, $envelope->getReplyTo(), AMQP_NOPARAM, array(
            'correlation_id' => $envelope->getCorrelationId(),
        ));

        //如果是用RPC模式, 需手动发送ACK应答,删除当前这条rpc消息
        $queue->ack($envelope->getDeliveryTag());
    })->pull();