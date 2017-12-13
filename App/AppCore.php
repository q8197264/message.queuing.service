<?php
namespace Cache\App;

use Cache\Core\Basis\Core;
/**
 * 自定义注册中间服务 (所有中间服务均由路由处理)
 * User: Liu xiaoquan
 * Date: 2017/6/28
 * Time: 18:25
 */
class AppCore extends Core
{
    //middle service
    protected $middleware = array(
        //'\Cache\Core\Storage\Redis\RedisServiceProvide',
//        \Cache\App\OAuth2\Provider::class,
    );

    protected $middlewareGroup = array(
        'web'=>'',
    );
}