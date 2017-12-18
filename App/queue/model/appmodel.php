<?php
namespace AmqpCall\model;

<<<<<<< HEAD
=======
use AmqpLib;
use AMQPChannel;
>>>>>>> origin/master
use AMQPExchange;
use AMQPExchangeException;
use AmqpCall\data\data;
use AmqpCall\lib\connection;
use AmqpCall\lib\channel;

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
<<<<<<< HEAD
            data::$connect = connection::getInstance($app, $config);
=======
            data::$connect = AmqpLib\connection::getInstance($app, $config);
>>>>>>> origin/master
            register_shutdown_function(function () {
                data::$connect->disconnect();
            });

<<<<<<< HEAD
            data::$channel = channel::getChannel(data::$connect);
=======
            data::$channel = AmqpLib\channel::getChannel(data::$connect);
>>>>>>> origin/master
        }
    }


    /**
     * 创建交换机
     * @param string $exc
     */
    public function setExchange($exname, $extype, $exflags)
    {
        if (!password_verify(json_encode(func_get_args()), base64_decode(data::$excflag))) {
            data::$excflag = base64_encode(password_hash(json_encode(func_get_args()), PASSWORD_BCRYPT));
            try {
                data::$exname   = $exname;
                data::$exchange = new AMQPExchange(data::$channel);
                empty($exname) OR data::$exchange->setName(data::$exname);
                empty($extype) OR data::$exchange->setType($extype);
                empty($exflags) OR data::$exchange->setFlags($exflags);
                empty($exname) OR data::$exchange->declareExchange();
            } catch(AMQPExchangeException $e) {
                die('falgs change '.$e->getMessage());
            }
        }
    }

}