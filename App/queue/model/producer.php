<?php
namespace AmqpCall\model;

use Closure;
use AmqpCall\data\data;
/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/12/4
 * Time: 14:46
 */
trait producer
{
    public function rpc($request, $routingkey, Closure $user_func)
    {
        if (empty(data::$queue)) {
            $this->addQueue('','', AMQP_AUTODELETE);//创建临时回调队列|AMQP_EXCLUSIVE
        }

        $corr_id = uniqid();
        $properties = array(
            'correlation_id' => $corr_id,
            'reply_to' => $callback_queue_name = data::$queue->getName(),
        );

        //push request
        data::$channel->begin();
        data::$exchange->publish($request, $routingkey, AMQP_NOPARAM, $properties);
        data::$channel->commit();

        //pull reply
        data::$queue->consume(function($envelope, $queue)use($user_func, $corr_id)
        {
            if ($envelope->getCorrelationId() == $corr_id) {
                $msg = $envelope->getBody();

                call_user_func($user_func, $msg);

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

            //启动事务 (应答机制与事务不可共存同一channel)
            data::$channel->begin();
            $res = $this->publish($data, $routingkey);
            if (in_array(0, $res)) {
                data::$channel->rollback();
            }else {
                data::$channel->commit();
            }
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