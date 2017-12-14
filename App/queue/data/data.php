<?php
namespace Cache\App\queue\data;
/**
 * 数据类
 * User: Liu xiaoquan
 * Date: 2017/12/6
 * Time: 17:40
 */
class data
{
    //config
    static $config;

    //resource
    static $connect;
    static $channel;
    static $exchange    = null;
    static $queue;


    /**
     * parameters
     */
    static $exname;

    static $routingkey;

    //args
    static $extype       = AMQP_EX_TYPE_DIRECT;//direct类型 AMQP_EX_TYPE_DIRECT、 AMQP_EX_TYPE_FANOUT、AMQP_EX_TYPE_HEADERS 、 AMQP_EX_TYPE_TOPIC

    /**
     * NOPARAM: 默认 完全禁用其他标志,如果你想临时禁用amqp.auto_ack设置起效
     * >php5.6 DURABLE: broker 重启后也会保留此 Exchange.
     * >php5.6 PASSIVE: 声明一个已存在的交换器的，如果不存在将抛出异常，这个一般用在consume端。因为一般produce端创建,在consume端建议设置成AMQP_PASSIVE,防止consume创建exchange
     * AUTODELETE: 所有绑定的的 Queue 都不再使用时, 此 Exchange 会自动删除.一个从未绑定任何队列的交换器不会自动删除
     *              当有队列bind到 AMQP_AUTODELETE 的交换器上之后，删除该队列。此时交换器也会删除。一般创建为临时交换器
     */
    static $exflags      = AMQP_DURABLE | AMQP_AUTODELETE;

    /**
     * DURABLE: 即使 broker 重启时, 此 queue 也不会被删除.
     * PASSIVE: 声明一个1个已存在的队列.
     * EXCLUSIVE: 只能有一个消费者, 并且当此消费者的连接断开时, 此 queue 会被删除.
     * AUTODELETE: 最后一个消费者取消订阅时被删除.
     */
    static $queueflags   = AMQP_DURABLE;

    //
    static $excflag = null;
}