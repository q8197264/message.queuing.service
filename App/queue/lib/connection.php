<?php
<<<<<<< HEAD
namespace AmqpCall\lib;
=======
namespace AmqpLib;
>>>>>>> origin/master

use AMQPConnection;
use AMQPConnectionException;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/16
 * Time: 15:07
 */
class connection
{
    private static $instance;
    private static $connect = null;


    private function __construct($app, array $config)
    {
        self::$connect = $this->goConnect($app, $config);
    }

    /**
     * @param       $app        container
     * @param array $config     amqp connect configuration
     *
     * @return AMQPConnection
     */
    public static function getInstance($app, array $config = array())
    {
        if (empty(self::$instance)) {
            self::$instance = new self($app, $config);
        }

        return self::$instance->getConnect();
    }

    //forbid clone
    final function __clone(){}

    /**
     * QPS proccess
     *
     * @param       $app
     * @param array $config
     *
     * @return config
     */
    protected static function checkConfig($app, array $config = array())
    {
        if (empty($config['vhost']) || empty($app['config']['queue'][$config['vhost']])) {
            $config = array('vhost' => '/');
        }
        $cluster = $app['config']['queue'][$config['vhost']];
        foreach ($cluster as $k=>$v) {
            if ($v['enable'] != true) {
                unset($cluster[$k]);
            }
        }
        $cluster = array_filter($cluster);
        $total = count($cluster);

        //时间轮徇：适用于QPS(每秒响应请求数)波动较小的业务
        $select = microtime(true)%$total;

        return array_merge($cluster[$select], $config);
    }

    /**
     * @param array $config
     *
     * @return AMQPConnection
     */
    protected function goConnect($app, array $config)
    {
        $connect = new AMQPConnection();
        while (true) {
            $config = self::checkConfig($app, $config);
            $connect->setHost($config['host']);
            $connect->setLogin($config['login']);
            $connect->setPassword($config['password']);
            $connect->setVhost($config['vhost']);
            try {
                $connect->connect();
                
                break;
            } catch (AMQPConnectionException $e) {
                $e->getMessage();//加入error日志
            }
        }

        return $connect;
    }

    /**
     * @return AMQPConnection|null
     */
    protected function getConnect()
    {
        return self::$connect;
    }

    public function __destory()
    {
        self::$connect->disconnect();
    }
}