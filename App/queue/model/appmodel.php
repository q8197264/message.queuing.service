<?php
namespace AmqpCall\model;

use AmqpCall\data\data;
use AmqpCall\lib\connection;
use AmqpCall\lib\channel;
use AMQPCall\lib\exchange;

use Cache\Core\Contracts\Basis\AppContainer;
use Cache\Core\Model\Model;

/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/11/21
 * Time: 15:03
 */
class appmodel extends Model
{
    use producer;
    use consumer;

    //初始化方法
    protected function init(AppContainer $app, array $config)
    {
        if (empty(data::$channel)) {
            data::$connect = connection::getInstance($app, $config);
            register_shutdown_function(function () {
                data::$connect->disconnect();
            });

            data::$channel = channel::getInstance(data::$connect);

        }
    }


    /**
     * 创建交换机
     * @param string $exc
     */
    public function setExchange($exname, $extype, $exflags)
    {
        data::$exname   = $exname;
        data::$exchange = exchange::getInstance(data::$channel->getChannel())->getExchange($exname, $extype, $exflags);
    }

}