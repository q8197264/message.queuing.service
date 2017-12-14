<?php
namespace Cache\App\queue\model;

use AMQPQueue;
use AMQPQueueException;

use Cache\App\queue\data\data;

/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/12/4
 * Time: 14:47
 */
trait consumer
{
    /**
     * 绑定队列
     * @param string $queueName
     * @param string $routekey
     * @param int    $queueflags
     */
    public function addQueue($queueName, $routingkey, $queueflags)
    {
        data::$routingkey = $routingkey;
        try {
            data::$queue = new AMQPQueue(data::$channel);
            if (!empty($queueName)) {
                data::$queue->setName($queueName);
            }
            data::$queue->setFlags($queueflags);
            data::$queue->declareQueue();
            if (!empty($routingkey) && !empty(data::$exname)) {
                data::$queue->bind(data::$exname, data::$routingkey);
            }
        } catch (AMQPQueueException $e) {
            die($e->getMessage());
        }
    }

    /**
     * 消费
     * @param $func
     */
    public function consume($func)
    {
        $consume_tag = sprintf("%s_%s_%s", php_uname('n'), time(), getmypid());
        //$this->queue->consume(array($this, 'processMessage'), AMQP_AUTOACK); //自动ACK应答
        data::$queue->consume($func, AMQP_NOPARAM, $consume_tag);

        data::$connect->disconnect();
    }
}