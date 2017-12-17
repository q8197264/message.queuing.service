<?php
/**
 * every vhost own one cluster on logic.
 *
 * 若要动态管理节点，可把此配置用数据库替代，放便进行管理与监控
 * for dynamic management nodes. this configuration can be replaced by a database
 * for manage or monitored is more quickly.
 * User: Liu xiaoquan
 * Date: 2017/11/21
 * Time: 14:21
 */
return array(
    '/'=>array(
        array(
            'host'     => '127.0.0.1',
            'port'     => '5672',
            'login'    => 'yaofang',
            'password' => 'yaofang',
            'enable' => true,
        ),
        array(
            'host'     => '192.168.1.99',
            'port'     => '5672',
            'login'    => 'yaofang',
            'password' => 'yaofang',
            'enable' => false,
        ),
    ),
    'test1.cn'=>array(
        array(
            'host'     => '127.0.0.1',
            'port'     => '5672',
            'login'    => 'yaofang',
            'password' => 'yaofang',
            'enable' => true,
        ),
        array(
            'host'     => '192.168.10.185',
            'port'     => '5672',
            'login'    => 'yaofang',
            'password' => 'yaofang',
            'enable' => true,
        ),
    ),
    'test2.cn'=>array(
        array(
            'host'     => '127.0.0.1',
            'port'     => '5672',
            'login'    => 'yaofang',
            'password' => 'yaofang',
            'enable' => true,
        ),
        array(
            'host'     => '192.168.10.185',
            'port'     => '5672',
            'login'    => 'yaofang',
            'password' => 'yaofang',
            'enable' => true,
        ),
        array(
            'host'     => '192.168.10.133',
            'port'     => '5672',
            'login'    => 'yaofang',
            'password' => 'yaofang',
            'enable' => true,
        ),
    ),
);