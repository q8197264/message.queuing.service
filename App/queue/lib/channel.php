<?php
namespace AmqpCall\lib;

use AMQPConnection;
use AMQPChannel;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/16
 * Time: 15:10
 */
class channel
{

    static function getChannel(AMQPConnection $connect) {
        $channel = new AMQPChannel($connect);
        $channel->setPrefetchCount(1);
        //var_dump(get_class_methods($channel));
        
        return $channel;
    }


    //set prefetchCount

    //set QPS

    //set transaction mode

    //set confirm mode
}