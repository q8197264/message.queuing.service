<?php
namespace Cache\App\queue\model;

use AMQPConnection;
use AMQPConnectionException;
use AMQPChannel;
use AMQPExchange;
use AMQPExchangeException;

use Cache\Core\Contracts\Basis\AppContainer;
use Cache\Core\Model\Model;
use Cache\App\queue\data\data;


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
        $config = $this->checkConfig($config);
        if (empty(data::$channel)) {
            try {
                data::$connect = new AMQPConnection($config);
                data::$connect->connect();
            } catch (AMQPConnectionException $e) {
                die($e->getMessage());
            }

            data::$channel = new AMQPChannel(data::$connect);
        }
    }

    private function checkConfig(array $config)
    {
        if (empty($config)) {
            return $this->app['config']['queue']['/'];
        }

        $config = isset($this->app['config']['queue'][$config['vhost']])
            ? array_merge($this->app['config']['queue'][$config['vhost']], $config)
            : array_merge($this->app['config']['queue']['/'], $config);

        return $config;
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
                data::$exchange->setName(data::$exname);
                empty($extype) OR data::$exchange->setType($extype);
                empty($exflags) OR data::$exchange->setFlags($exflags);
                data::$exchange->declareExchange();
            } catch(AMQPExchangeException $e) {
                die('falgs change '.$e->getMessage());
            }
        }
    }

}