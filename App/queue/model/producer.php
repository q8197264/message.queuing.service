<?php
namespace Cache\App\queue\model;

use Cache\App\queue\data\data;
/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/12/4
 * Time: 14:46
 */
trait producer
{
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
    public function send(array $data, $routeingkey)
    {
        register_shutdown_function(function () {
            data::$connect->disconnect();
        });

        if (empty($data)) {
            return null;
        }

        $corr_id = uniqid();
        $properties = [
            'correlation_id' => $corr_id,
            'reply_to' => $callback_queue_name=data::$queue->getName(),
        ];

        //发布消息
        data::$channel->startTransaction();
        $res = '';
        foreach ($data as $k=>$v) {
            $v = is_string($v) ? $v : json_encode($v);
            $res[$k] = data::$exchange->publish($v, $routeingkey, false, $properties);
        }
        data::$channel->commitTransaction();//提交事务

        data::$queue->consume(function($envelope, $queue)use($corr_id)
        {
            if ($envelope->getCorrelationId() == $corr_id) {
                $msg = $envelope->getBody();
                var_dump('Received Data: ' . $msg);
                $queue->nack($envelope->getDeliveryTag());

                return false;
            }
        });

        //        $this->connect->disconnect();
        return $res;
    }

}