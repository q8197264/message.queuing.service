<?php
namespace Cache\Core\Router;

use Cache\Core\Contracts\Router\RoutingServiceProvider as Routercontract;

/**
 * Created by PhpStorm.
 * User: Liu xiaoquan
 * Date: 2017/10/13
 * Time: 18:12
 */
class RouteServiceProvider implements Routercontract
{
    function boot()
    {
        var_dump('Route:'.__METHOD__);
    }

    function register()
    {
        var_dump('Route:'.__METHOD__);
    }

    function isDeferred()
    {}

//    function __call($method, $args)
//    {
//        var_dump($method);
//    }
}