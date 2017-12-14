<?php
namespace Cache\App\queue\model;

use Cache\App\queue\data\data;
/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/12/4
 * Time: 14:46
 */
register_shutdown_function(function () {
    data::$connect->disconnect();
});
trait producer
{
    public function rpc($request, $routingkey, \Closure $user_func)
    {
        $this->addQueue('','', AMQP_EXCLUSIVE);//临时队列

        $corr_id = uniqid();
        $properties = array(
            'correlation_id' => $corr_id,
            'reply_to' => $callback_queue_name = data::$queue->getName(),
        );

        data::$exchange->publish($request, $routingkey, AMQP_NOPARAM, $properties);

        data::$queue->consume(function($envelope, $queue)use($user_func, $corr_id)
        {
            if ($envelope->getCorrelationId() == $corr_id) {
                $msg = $envelope->getBody();

                call_user_func($user_func, $queue->getName().':'.$msg);

                $queue->nack($envelope->getDeliveryTag());

                return false;
            }
        });
    }

    /**
     * @param array $data
     * @param       $routeingkey
     * @param int   $flags
     * @param array $attributes content_type 	  	text/plain
                                content_encoding 	  	NULL
                                message_id 	  	NULL
                                user_id 	  	NULL
                                app_id 	  	NULL
                                delivery_mode 	  	NULL
                                priority 	  	NULL
                                timestamp 	  	NULL
                                expiration 	  	NULL
                                type 	  	NULL
                                reply_to 	  	NULL
     *
     * @return null|string
     */
    public function send($data, $routingkey)
    {
        if (empty($data)) {
            return null;
        }

        //发布消息
        $data = is_string($data) ? array($data) : $data;
        if (count($data) > 1) {
            //启动事务
            data::$channel->startTransaction();
            $res = $this->publish($data, $routingkey);
            data::$channel->commitTransaction();
        } else {
            $res = $this->publish($data, $routingkey);
        }

        return $res;
    }

    private function publish($data, $routingkey)
    {
        $res = '';
        foreach ($data as $k=>$v) {
            $v = is_string($v) ? $v : json_encode($v);
            $res[$k] = data::$exchange->publish($v, $routingkey);
        }

        return $res;
    }

}